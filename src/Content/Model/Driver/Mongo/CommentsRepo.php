<?php
namespace Module\Content\Model\Driver\Mongo;

use Module\Content\Interfaces\Model\Entity\iEntityComment;
use Module\Content\Interfaces\Model\Repo\iRepoComments;
use Module\Content\Model\Driver\Mongo;

use Module\MongoDriver\Model\Repository\aRepository;
use MongoDB\BSON\ObjectID;


class CommentsRepo
    extends aRepository
    implements iRepoComments
{
    /**
     * Initialize Object
     *
     */
    protected function __init()
    {
        $this->setModelPersist(new Mongo\EntityComment);
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
     * Insert Comment Entity
     *
     * @param iEntityComment $entity
     *
     * @return iEntityComment Include persistence insert identifier
     */
    function insert(iEntityComment $entity)
    {
        $givenIdentifier = $entity->getUid();
        $givenIdentifier = $this->genNextIdentifier($givenIdentifier);

        if (!$dateCreated = $entity->getDateTimeCreated())
            $dateCreated = new \DateTime();


        # Convert given entity to Persistence Entity Object To Insert
        $entityMongo = new Mongo\EntityComment;
        $entityMongo
            ->setUid($givenIdentifier)
            ->setContent( $entity->getContent() )
            // We Consider All Item Liked Has _id from Mongo Collection
            ->setItemIdentifier( $this->genNextIdentifier($entity->getItemIdentifier()) )
            ->setOwnerIdentifier( $entity->getOwnerIdentifier() )
            ->setModel( $entity->getModel() )
            ->setVoteCount( $entity->getVoteCount() )
            ->setStat( $entity->getStat() )
            ->setDateTimeCreated($dateCreated)
        ;

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
     * @param iEntityComment $entity
     *
     * @return mixed
     */
    function save(iEntityComment $entity)
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
     * Remove a Comment Entity
     *
     * @param iEntityComment $entity
     *
     * @return int
     */
    function remove(iEntityComment $entity)
    {
        $r = $this->_query()->deleteMany([
            '_id' => $this->genNextIdentifier( $entity->getUid() )
        ]);

        return $r->getDeletedCount();
    }


    /**
     * Find Match By Given UID
     *
     * @param string|mixed $uid
     *
     * @return iEntityComment|false
     */
    function findOneMatchUid($uid)
    {
        $r = $this->_query()->findOne([
            '_id' => $this->genNextIdentifier($uid),
        ]);

        return ($r) ? $r : false;
    }

    /**
     * Find Entities Match With Given Identifier And Model
     *
     * @param mixed    $item_identifier
     * @param string   $model
     * @param int|null $skip
     * @param int|null $limit
     *
     * @return \Traversable
     */
    function findByItemIdentifierOfModel($item_identifier, $model, $skip = null, $limit = null)
    {
        $r = $this->_query()->find(
            [
                // We Consider All Item Liked Has _id from Mongo Collection
                'item_identifier' => $this->genNextIdentifier($item_identifier),
                'model'           => $model
            ],
            [
                'limit' => $limit,
                'skip'  => $skip,
            ]
        );

        return $r;
    }
}
