<?php
namespace Module\Content\Model\Driver\Mongo;

use Module\Content\Model\Driver\Mongo;

use Module\Content\Interfaces\Model\Repo\iRepoPosts;
use Module\Content\Model\Entity\EntityPost;
use Module\Content\Model\Entity\MemberObject;
use Module\MongoDriver\Model\Repository\aRepository;
use MongoDB\BSON\ObjectID;
use MongoDB\Operation\FindOneAndUpdate;
use Poirot\Std\Hydrator\HydrateGetters;

// TODO Just for tracking likes by me we embed whole members that like post
class PostsRepo
    extends aRepository
    implements iRepoPosts
{
    /**
     * Initialize Object
     *
     */
    protected function __init()
    {
        if (! $this->persist )
            $this->setModelPersist(new Mongo\EntityPost);
    }


    /**
     * Generate next unique identifier to persist
     * data with
     *
     * @param null|string $id
     *
     * @return mixed
     * @throws \Exception
     */
    function attainNextIdentifier($id = null)
    {
        try {
            $objectId = ($id !== null) ? new ObjectID( (string)$id ) : new ObjectID;
        } catch (\Exception $e) {
            throw new \Exception(sprintf('Invalid Persist (%s) Id is Given.', $id));
        }

        return $objectId;
    }


    /**
     * Persist Content Post
     *
     * - if entity has no identifier used ::nextIdentifier
     *   to assign something new
     *
     * @param EntityPost $entity
     *
     * @return EntityPost Contains inserted uid
     */
    function insert(EntityPost $entity)
    {
        $givenIdentifier = $entity->getUid();
        $givenIdentifier = $this->attainNextIdentifier($givenIdentifier);

        if (!$dateCreated = $entity->getDateTimeCreated())
            $dateCreated = new \DateTime();


        # Convert given entity to Persistence Entity Object To Insert
        $entityMongo = new Mongo\EntityPost($entity);
        $entityMongo->setUid($givenIdentifier);
        $entityMongo->setDateTimeCreated($dateCreated);
        if ( $entity->getOwnerIdentifier() )
            $entityMongo->setOwnerIdentifier(
                $this->attainNextIdentifier($entity->getOwnerIdentifier())
            );


        # Persist BinData Record
        $r = $this->_query()->insertOne($entityMongo);

        # Give back entity with given id and meta record info
        $entity = clone $entity;
        $entity->setUid( $r->getInsertedId() );
        return $entity;
    }

    /**
     * Save Entity By Insert Or Update
     *
     * @param EntityPost $entity
     *
     * @return mixed
     */
    function save(EntityPost $entity)
    {
        if ($entity->getUid()) {
            // It Must Be Update

            /* Currently With Version 1.1.2 Of MongoDB driver library
             * Entity Not Replaced Entirely
             *
             * $this->_query()->updateOne(
                [
                    '_id' => $entity->getUid(),
                ]
                , $entity
                , ['upsert' => true]
            );*/

            $this->_query()->deleteOne([
                '_id' => $this->attainNextIdentifier( $entity->getUid() ),
            ]);
        }

        $entity = $this->insert($entity);
        return $entity;
    }

    /**
     * // TODO post entity mongo can't directly send to response because getUid() is ignored
     * //      it must converted to entity original by setters without unwanted property like mongo_date, ..
     * //      maybe the hydration match can help to only transport similar data
     *
     * Find Match By Given UID
     *
     * @param string|mixed $uid
     *
     * @return EntityPost|false
     */
    function findOneMatchUid($uid)
    {
        /** @var \Module\Content\Model\Driver\Mongo\EntityPost $r */
        $r = $this->_query()->findOne([
            '_id' => $this->attainNextIdentifier($uid),
        ]);

        if (! $r )
            return false;


        return $r;
    }

    /**
     * Delete Entity With Given UID
     *
     * @param mixed $uid
     *
     * @return int Delete Count
     */
    function deleteOneMatchUid($uid)
    {
        $uid = $this->attainNextIdentifier($uid);

        # Find and delete object
        $r = $this->_query()->deleteOne([
            '_id' => $this->attainNextIdentifier($uid),
        ]);

        return $r->getDeletedCount();
    }

    /**
     * Lock Post With Given UID
     *
     * @param mixed $uid
     *
     * @return mixed
     */
    function lockOneMatchUid($uid)
    {
        $r = $this->_query()->updateOne(
            [
                '_id' => $this->attainNextIdentifier($uid),
            ]
            , [
                '$set' => [
                    'stat' => EntityPost::STAT_LOCKED,
                ]
            ]
        );

        return ($r) ? $r : false;
    }

    /**
     * Change Post Status By UID
     *
     * @param mixed $uid
     * @param string $status
     *
     * @return bool|\MongoDB\UpdateResult
     */
    function changeStat($uid, $status)
    {
        $r = $this->_query()->updateOne(
            [
                '_id' => $this->attainNextIdentifier($uid),
            ]
            , [
                '$set' => [
                    'stat' => $status,
                ]
            ]
        );

        return ($r) ? $r : false;
    }

    /**
     * @deprecated 
     * Change Post Status By UID
     *
     * @param mixed $uid
     * @param string $status
     *
     * @return bool|\MongoDB\UpdateResult
     */
    function changeStatWithUpstreamId($uid, $status)
    {
        $r = $this->_query()->updateOne(
            [
                'content.campaign_id' => (int) $uid,
            ]
            , [
                '$set' => [
                    'stat' => $status,
                ]
            ]
        );

        return ($r) ? $r : false;
    }

    /**
     * Find Entities Match With Given Expression
     *
     * !! Consider Mongo Indexes When Using Custom Conditions !!
     *
     * @param array    $expression Filter expression
     * @param int|null $offset
     * @param int|null $limit
     *
     * @return \Traversable
     */
    function findAll(array $expression, $offset = null, $limit = null)
    {
        $expression = \Module\MongoDriver\parseExpressionFromArray($expression);
        $condition  = \Module\MongoDriver\buildMongoConditionFromExpression($expression);

        if ($offset)
            $condition = [
                    '_id' => [
                        '$lt' => $this->attainNextIdentifier($offset),
                    ]
                ] + $condition;

        $r = $this->_query()->find(
            $condition
            , [
                'limit' => $limit,
                'sort'  => [
                    '_id' => -1,
                ],
            ]
        );

        return $r;
    }

    /**
     * Find All Match By Given Owner UIDs List
     *
     * !! Consider Mongo Indexes When Using Custom Conditions !!
     *
     * @param string $ownerIdentifier Owner Identifier
     * @param array  $expression      Filter expression
     * @param string $offset          Offset is MongoID
     * @param int    $limit
     *
     * @return \Traversable
     */
    function findAllMatchWithOwnerId($ownerIdentifier, array $expression = null, $offset = null, $limit = null)
    {
        $expression = \Module\MongoDriver\parseExpressionFromArray($expression);
        $condition  = \Module\MongoDriver\buildMongoConditionFromExpression($expression);

        $condition = [
            'owner_identifier' => $this->attainNextIdentifier($ownerIdentifier),
        ] + $condition;

        if ($offset)
            $condition = [
                '_id' => [
                    '$lt' => $this->attainNextIdentifier($offset),
                ]
            ] + $condition;


        $r = $this->_query()->find(
            $condition
            , [
                'limit' => $limit,
                'sort'  => [
                    '_id' => -1,
                ],
            ]
        );

        return $r;
    }

    /**
     * Get Count All Match By Given Owner UID
     *
     * @param string $ownerIdentifier Owner Identifier
     *
     * @return int
     */
    function getCountMatchWithOwnerId($ownerIdentifier)
    {
        $r = $this->_query()->count(
            [ 'owner_identifier' => $this->attainNextIdentifier($ownerIdentifier) ]
        );

        return $r;
    }

    /**
     * Find All Match By Given UIDs List
     *
     * !! Consider Mongo Indexes When Using Custom Conditions !!
     *
     * @param []mixed      $uids
     * @param array|string $expression Filter expression
     *
     * @return \Traversable
     */
    function findAllMatchUidWithin($uids, $expression = null)
    {
        $objIds = [];
        foreach ($uids as $id) {
            if ( empty($id) )
                continue;

            $objIds[] = $this->attainNextIdentifier($id);
        }


        // Query Condition By Expression

        $queryConditions = [
            '_id' => [
                '$in' => $objIds
            ]
        ];

        if ($expression !== null) {
            if (is_string($expression))
                $expression = \Module\MongoDriver\parseExpressionFromString($expression);
            else
                $expression = \Module\MongoDriver\parseExpressionFromArray($expression);

            $queryConditions
                += \Module\MongoDriver\buildMongoConditionFromExpression($expression);
        }

        $r = $this->_query()->find(
            $queryConditions,
            [
                'sort' => [
                    '_id' => -1,
                ]
            ]
        );

        return $r;
    }

    /**
     * Set a Like On Post By Given ID
     *
     * @param string       $content_id
     * @param MemberObject $member
     *
     * @return EntityPost\LikesObject
     */
    function insertLikeEntry($content_id, MemberObject $member)
    {
        /** @var EntityPost $r */
        $r = $this->_query()->findOneAndUpdate(
            [
                '_id' => $this->attainNextIdentifier($content_id),
            ],
            [
                // Keep Track Of All Users That Like an Entity
                '$addToSet' => [
                    'likes.total_members' => [
                        '$each' => [ $member->getUid() ],
                    ],
                ],
                // More Info About Latest Users Who Like an Entity
                '$push' => [
                    'likes.latest_members' => [
                        '$each' => [ \Poirot\Std\cast($member)->toArray() ],
                        '$position' => 0,
                        '$slice' => 10,
                    ],
                ],
                '$inc'  => [
                    'likes.count' => 1,
                ]
            ],
            [
                'projection' => [
                    'likes' => 1,
                ],
                'returnDocument' => FindOneAndUpdate::RETURN_DOCUMENT_AFTER
            ]
        );

        return ($r) ? $r->getLikes() : null;
    }

    /**
     * Remove a Like On Post By Given ID
     *
     * @param string       $content_id
     * @param MemberObject $member
     *
     * @return EntityPost\LikesObject
     */
    function removeLikeEntry($content_id, MemberObject $member)
    {
        /** @var EntityPost $r */
        $r = $this->_query()->findOneAndUpdate(
            [
                '_id' => $this->attainNextIdentifier($content_id),
            ],
            [
                '$pull' => [
                    'likes.latest_members' => [
                        'uid' => $member->getUid(),
                    ],
                    'likes.total_members' => $member->getUid(),
                ],
                '$inc'  => [
                    'likes.count' => -1,
                ]
            ],
            [
                'projection' => [
                    'likes' => 1,
                ],
                'returnDocument' => FindOneAndUpdate::RETURN_DOCUMENT_AFTER
            ]
        );

        return ($r) ? $r->getLikes() : null;
    }

    /**
     * @inheritdoc
     */
    function findUserLatestPost($uid)
    {
        $expression = \Module\MongoDriver\parseExpressionFromString('stat=publish&stat_share=public');
        $condition  = \Module\MongoDriver\buildMongoConditionFromExpression($expression);
        $condition += [ 'owner_identifier' => $this->attainNextIdentifier($uid) ];

        $r = $this->_query()->findOne(
            $condition,
            [
                'sort' => [
                    '_id'   => -1,
                    'limit' => 1
                ]
            ]
        );
        return ($r) ? $r : null;
    }

    /**
     * @param array|object $filter  Query by which to filter documents
     * @param array        $options Command options
     *
     * @return int
     */
    protected function count($filter, array $options)
    {
        return $this
            ->gateway
            ->selectCollection($this->collection_name)
            ->count($filter, $options);
    }

    /**
     * @inheritdoc
     */
    function countNewPostsAfter($offset)
    {
        return $this->count(
            [
                '_id' => [ '$gt' => $this->attainNextIdentifier($offset), ]
            ],
            [
                'sort'  => [ '_id' => 1, ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    function countUserRepostsNewerThan($ownerIdentifier, \DateTime $dateTime)
    {
        return $this->count(
            [
                'owner_identifier'          => $this->attainNextIdentifier($ownerIdentifier),
                'content.content_type'      => 'repost',
                'date_time_created_mongo'   => [
                    '$gte' => New \MongoDB\BSON\UTCDateTime($dateTime->getTimestamp() * 1000)
                ],
            ],
            []
        );
    }
}
