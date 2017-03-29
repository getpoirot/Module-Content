<?php
namespace Module\Content\Model\Driver\Mongo;

use Module\Content\Model\Driver\Mongo;

use Module\Content\Interfaces\Model\Repo\iRepoPosts;
use Module\Content\Model\Entity\EntityPost;
use Module\Content\Model\Exception\exDuplicateEntry;
use Module\MongoDriver\Model\Repository\aRepository;
use MongoDB\BSON\ObjectID;


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


        $givenIdentifier = $this->genNextIdentifier($givenIdentifier);

        if (!$dateCreated = $entity->getDateTimeCreated())
            $dateCreated = new \DateTime();

        # Convert given entity to Persistence Entity Object To Insert
        $post = new Mongo\EntityPost;
        $post
            ->setUid($givenIdentifier)
            ->setContent($entity->getContent())
            ->setStat($entity->getStat())
            ->setStatShare($entity->getStatShare())
            ->setLocation($entity->getLocation())
            ->setDateTimeCreated($dateCreated)
        ;


        # Persist BinData Record
        $r = $this->_query()->insertOne($post);
        # Give back entity with given id and meta record info
        $post->setUid( $r->getInsertedId() );
        return $post;
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
        /** @var MongoEntityPost $r */
        $r = $this->_query()->findOne([
            '_id' => $this->genNextIdentifier($uid),
        ]);

        return ($r) ? $r : false;
    }
}
