<?php
namespace Module\Content\Model\Driver\Mongo;

use Module\MongoDriver\Services\aServiceRepository;


class PostsRepoService
    extends aServiceRepository
{
    /** @var string Service Name */
    protected $name = 'Posts';


    /**
     * Return new instance of Repository
     *
     * @param \MongoDB\Database  $mongoDb
     * @param string             $collection
     * @param string|object|null $persistable
     *
     * @return PostsRepo
     */
    function newRepoInstance($mongoDb, $collection, $persistable = null)
    {
        $repo = new PostsRepo($mongoDb, $collection, $persistable);
        return $repo;
    }
}
