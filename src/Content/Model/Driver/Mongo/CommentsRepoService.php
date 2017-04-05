<?php
namespace Module\Content\Model\Driver\Mongo;

use Module\MongoDriver\Services\aServiceRepository;


class CommentsRepoService
    extends aServiceRepository
{
    /** @var string Service Name */
    protected $name = 'Comments';


    /**
     * Return new instance of Repository
     *
     * @param \MongoDB\Database  $mongoDb
     * @param string             $collection
     * @param string|object|null $persistable
     *
     * @return CommentsRepo
     */
    function newRepoInstance($mongoDb, $collection, $persistable = null)
    {
        $repo = new CommentsRepo($mongoDb, $collection, $persistable);
        return $repo;
    }
}
