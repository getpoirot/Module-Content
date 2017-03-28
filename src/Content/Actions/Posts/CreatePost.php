<?php
namespace Module\Content\Actions\Posts;

use Module\Content\Actions\aAction;
use Module\Content\Interfaces\Model\iRepoPosts;


class CreatePost
    extends aAction
{
    /** @var iRepoPosts */
    protected $repoPosts;


    /**
     * ValidatePage constructor.
     *
     * @param iRepoPosts $repoPosts @IoC /module/content/services/repository/Posts
     */
    function __construct(iRepoPosts $repoPosts)
    {
        $this->repoPosts = $repoPosts;
    }

    function __invoke()
    {
        // TODO: Implement __invoke() method.
    }
}
