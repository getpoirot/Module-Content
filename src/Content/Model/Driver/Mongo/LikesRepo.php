<?php
namespace Module\Content\Model\Driver\Mongo;

use Module\Content\Model\Driver\Mongo;
use Module\Content\Interfaces\Model\Entity\iEntityLike;
use Module\Content\Interfaces\Model\Repo\iRepoLikes;

use Module\MongoDriver\Model\Repository\aRepository;
use MongoDB\BSON\ObjectID;
use MongoDB\BSON\UTCDatetime;


class LikesRepo
    extends aRepository
    implements iRepoLikes
{
    /**
     * Initialize Object
     *
     */
    protected function __init()
    {
        if (!$this->persist)
            $this->setModelPersist(new Mongo\EntityLike);
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
    function genNextIdentifier($id = null)
    {
        try {
            $objectId = ($id !== null) ? new ObjectID( (string)$id ) : new ObjectID;
        } catch (\Exception $e) {
            throw new \Exception(sprintf('Invalid Persist (%s) Id is Given.', $id));
        }

        return $objectId;
    }

    /**
     * Save Like Entity
     *
     * - like entity with same [model,item,owner] fields
     *   must only store once
     *
     * @param iEntityLike $entity
     *
     * @return iEntityLike|null Return Clone copy if changed otherwise given Entity
     */
    function save(iEntityLike $entity)
    {
        $r = $this->_query()->updateOne(
            [
                '_uid' => $entity->get_Uid(),

                /* Use UID that combine all of fields into hashed value
                 *
                'owner_identifier' => $entity->getOwnerIdentifier(),
                                      // We Consider All Item Liked Has _id from Mongo Collection
                'item_identifier'  => $this->genNextIdentifier( $entity->getItemIdentifier() ),
                'model'            => $entity->getModel(),
                */
            ]
            , [
                '$set' => [
                    'owner_identifier' => $entity->getOwnerIdentifier(),
                                       // We Consider All Item Liked Has _id from Mongo Collection
                    'item_identifier'  => $this->genNextIdentifier( $entity->getItemIdentifier() ),
                    'model'            => $entity->getModel(),
                ],
                '$setOnInsert' => [
                    'datetime_created_mongo' => new UTCDatetime($entity->getDatetimeCreated()->getTimestamp() * 1000)
                ]
            ]
            , ['upsert' => true]
        );


        if ( $r->getModifiedCount() || $r->getUpsertedCount() )
            return clone $entity;

        return null;
    }

    /**
     * Remove Like Entity
     *
     * - like entity with same [model,item,owner] fields
     *
     * @param iEntityLike $entity
     *
     * @return int
     */
    function remove(iEntityLike $entity)
    {
        $r = $this->_query()->deleteMany([
            '_uid' => $entity->get_Uid()
        ]);

        return $r->getDeletedCount();
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
                'sort' => [
                    '_id' => -1
                ],
            ]
        );

        return $r;
    }

    /**
     * Find Entities Liked By Owner In Model X
     *
     * @param mixed  $owner_identifier
     * @param string $model
     * @param int|null $skip
     * @param int|null $limit
     *
     * @return \Traversable
     */
    function findAllItemsOfOwnerAndModel($owner_identifier, $model, $skip = null, $limit = null)
    {
        $r = $this->_query()->find(
            [
                'owner_identifier' => (string) $owner_identifier,
                'model'            => $model
            ],
            [
                'limit' => $limit,
                'skip'  => $skip,
            ]
        );

        return $r;
    }
}
