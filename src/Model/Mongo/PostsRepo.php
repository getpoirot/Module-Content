<?php
namespace Module\Content\Model\Mongo;

use Module\Content\Interfaces\Model\iRepoPosts;
use Module\MongoDriver\Model\Repository\aRepository;


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
        # $this->setModelPersist();
    }

}
