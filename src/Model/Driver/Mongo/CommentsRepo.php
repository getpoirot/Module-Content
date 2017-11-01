<?php
namespace Module\Content\Model\Driver\Mongo;

use Module\Content\Interfaces\Model\Entity\iEntityComment;
use Module\Content\Interfaces\Model\Repo\iRepoComments;
use Module\Content\Model\Driver\Mongo;

use Module\MongoDriver\Model\Repository\aRepository;
use MongoDB\BSON\ObjectID;
use MongoDB\Operation\FindOneAndUpdate;


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
        if (!$this->persist)
            $this->setModelPersist(new Mongo\EntityComment);
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
     * Insert Comment Entity
     *
     * @param iEntityComment $entity
     *
     * @return iEntityComment Include persistence insert identifier
     */
    function insert(iEntityComment $entity)
    {
        $givenIdentifier = $entity->getUid();
        $givenIdentifier = $this->attainNextIdentifier($givenIdentifier);

        if (!$dateCreated = $entity->getDateTimeCreated())
            $dateCreated = new \DateTime();


        # Convert given entity to Persistence Entity Object To Insert
        $entityMongo = new Mongo\EntityComment;
        $entityMongo
            ->setUid($givenIdentifier)
            ->setContent( $entity->getContent() )
            // We Consider All Item Liked Has _id from Mongo Collection
            ->setItemIdentifier( $this->attainNextIdentifier($entity->getItemIdentifier()) )
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
                '_id' => $this->attainNextIdentifier( $entity->getUid() ),
            ]);
        }

        $entity = $this->insert($entity);
        return $entity;
    }

    /**
     * Soft Remove a Comment Entity
     *
     * @param iEntityComment $entity
     *
     * @return iEntityComment
     */
    function updateStatToDeleted    (iEntityComment $entity)
    {
        $r = $this->_query()->findOneAndUpdate(
            [
                '_id' => $this->attainNextIdentifier( $entity->getUid() )
            ]
            , [
                '$set' => [
                    'stat' => iRepoComments::STAT_DELETED,
                ],
            ]
            , [
                'returnDocument' => FindOneAndUpdate::RETURN_DOCUMENT_AFTER
            ]
        );

        return $r;
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
            '_id' => $this->attainNextIdentifier($uid),
        ]);

        return ($r) ? $r : false;
    }

    /**
     * Find Entities Match With Given Expression
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
}
