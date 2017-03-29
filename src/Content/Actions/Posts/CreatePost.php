<?php
namespace Module\Content\Actions\Posts;

use Module\Content\Actions\aAction;
use Module\Content\Interfaces\Model\Repo\iRepoPosts;
use Module\Content\Model\Entity\EntityPost;


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


    /**
     * Create Post Content
     *
     * - trigger post.create event to subscribber
     *
     * @param EntityPost $post
     *
     * @return EntityPost
     */
    function __invoke(EntityPost $post = null)
    {
        $persistEntity = $this->repoPosts->insert($post);

        // TODO trigger Post Created

        return $persistEntity;
    }
}
