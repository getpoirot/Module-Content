<?php
namespace Module\Content\Actions;

use Module\Content\Interfaces\Model\Repo\iRepoLikes;
use Module\Content\Interfaces\Model\Repo\iRepoPosts;
use Module\Content\Model\Entity\EntityLike;


class ListPostsLikedByUser
{
    /** @var iRepoLikes */
    protected $repoLikes;
    protected $repoPosts;


    /**
     * Construct
     *
     * @param iRepoLikes   $repoLikes @IoC /module/content/services/repository/Likes
     * @param iRepoPosts   $repoPosts @IoC /module/content/services/repository/Posts
     */
    function __construct(iRepoLikes $repoLikes, iRepoPosts $repoPosts)
    {
        $this->repoLikes = $repoLikes;
        $this->repoPosts = $repoPosts;
    }


    /**
     * Find posts liked by the user owner
     *
     * @param string   $ownerIdentifier Owner Identifier
     * @param int|null $offset
     * @param int|null $limit
     *
     * @return \Traversable
     */
    function __invoke($ownerIdentifier = null, $offset = null, $limit = 30)
    {
        $likedPost = $this->repoLikes->findAllItemsOfOwnerAndModel(
            $ownerIdentifier
            , EntityLike::MODEL_POSTS
            , $offset
            , $limit
        );

        return $likedPost;
    }
}
