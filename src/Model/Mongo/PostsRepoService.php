<?php
namespace Module\Content\Model\Mongo;

use Module\MongoDriver\Services\aServiceRepository;


class PostsRepoService
    extends aServiceRepository
{
    /** @var string Service Name */
    protected $name = 'Posts';


    /**
     * Return new instance of Repository
     *
     * @param \MongoDB\Database $mongoDb
     * @param string            $collection
     *
     * @return PostsRepo
     */
    function newRepoInstance($mongoDb, $collection)
    {
        $repo = new PostsRepo($mongoDb, $collection);
        return $repo;
    }
}
