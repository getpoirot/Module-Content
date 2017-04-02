<?php
namespace Module\Content\Model\Driver\Mongo;

use Module\MongoDriver\Services\aServiceRepository;


class LikesRepoService
    extends aServiceRepository
{
    /** @var string Service Name */
    protected $name = 'Likes';


    /**
     * Return new instance of Repository
     *
     * @param \MongoDB\Database $mongoDb
     * @param string            $collection
     *
     * @return LikesRepo
     */
    function newRepoInstance($mongoDb, $collection)
    {
        $repo = new LikesRepo($mongoDb, $collection);
        return $repo;
    }
}
