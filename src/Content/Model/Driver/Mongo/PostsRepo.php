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
        if (!$this->persist)
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
        $entityMongo = new Mongo\EntityPost(new HydrateGetters($entity));
        $entityMongo->setUid($givenIdentifier);
        $entityMongo->setDateTimeCreated($dateCreated);

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

        return ($r) ? $r : false;
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
    function findAll($expression, $offset = null, $limit = null)
    {
        $condition = \Module\MongoDriver\buildMongoConditionFromExpression($expression);

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
    function findAllMatchWithOwnerId($ownerIdentifier, $expression = null, $offset = null, $limit = null)
    {
        $condition = \Module\MongoDriver\buildMongoConditionFromExpression($expression);

        $condition = [
            'owner_identifier' => (string) $ownerIdentifier,
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
        foreach ($uids as $id)
            $objIds[] = $this->attainNextIdentifier($id);


        // Query Condition By Expression

        $queryConditions = [
            '_id' => [
                '$in' => $objIds
            ]
        ];

        if ($expression !== null) {
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
}
