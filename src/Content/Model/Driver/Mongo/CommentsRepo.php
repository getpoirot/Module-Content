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
}
