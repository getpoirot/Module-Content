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
     * @param \MongoDB\Database $mongoDb
     * @param string            $collection
     *
     * @return CommentsRepo
     */
    function newRepoInstance($mongoDb, $collection)
    {
        $repo = new CommentsRepo($mongoDb, $collection);
        return $repo;
    }
}
