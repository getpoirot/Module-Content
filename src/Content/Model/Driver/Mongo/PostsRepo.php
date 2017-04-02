<?php
namespace Module\Content\Model\Driver\Mongo;

use Module\Content\Model\Driver\Mongo;

use Module\Content\Interfaces\Model\Repo\iRepoPosts;
use Module\Content\Model\Entity\EntityPost;
use Module\Content\Model\Entity\MemberObject;
use Module\Content\Model\Exception\exDuplicateEntry;
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
        $this->setModelPersist(new Mongo\EntityPost);
    }


    /**
     * Generate next unique identifier to persist
     * data with
     *
     * @param null|string $id
     *
     * @return mixed
     */
    function genNextIdentifier($id = null)
    {
        return ($id !== null) ? new ObjectID( (string)$id ) : new ObjectID;
    }


    /**
     * Persist Content Post
     *
     * - check given entity identifier not exists; must be unique
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
        if ($givenIdentifier && false !== $this->findOneByUID($givenIdentifier))
            throw new exDuplicateEntry(sprintf(
                'Content with UID (%s) exists.'
                , (string) $givenIdentifier
            ), 400);


        $givenIdentifier  = $this->genNextIdentifier($givenIdentifier);

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
                '_id' => $this->genNextIdentifier( $entity->getUid() ),
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
    function findOneByUID($uid)
    {
        /** @var \Module\Content\Model\Driver\Mongo\EntityPost $r */
        $r = $this->_query()->findOne([
            '_id' => $this->genNextIdentifier($uid),
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
    function deleteOneByUID($uid)
    {
        $uid = $this->genNextIdentifier($uid);

        # Find and delete object
        $r = $this->_query()->deleteOne([
            '_id' => $this->genNextIdentifier($uid),
        ]);

        return $r->getDeletedCount();
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
                '_id' => $this->genNextIdentifier($content_id),
            ],
            [
                '$push' => [
                    'likes.members' => [
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
                '_id' => $this->genNextIdentifier($content_id),
            ],
            [
                '$pull' => [
                    'likes.members' => [
                            'uid' => $member->getUid(),
                    ],
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
