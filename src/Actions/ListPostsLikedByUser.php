<?php
namespace Module\Content\Actions;

use Module\Content\Interfaces\Model\Repo\iRepoLikes;
use Module\Content\Interfaces\Model\Repo\iRepoPosts;
use Module\Content\Model\Entity\EntityLike;
use Module\Content\Model\Entity\EntityPost;


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
     * @param string   $owner_identifier Owner Identifier
     * @param int|null $skip
     * @param int|null $limit
     *
     * @return \Traversable
     */
    function __invoke($owner_identifier = null, $skip = null, $limit = 30)
    {
        $likedPost = $this->repoLikes->findAllItemsOfOwnerAndModel(
            $owner_identifier
            , EntityLike::MODEL_POSTS
            , $skip
            , $limit
        );

        /** @var EntityLike $like */
        $posts_id = [];
        foreach ($likedPost as $like) {
            $posts_id[] = $like->getItemIdentifier();
        }

        $posts = $this->repoPosts->findAllMatchUidWithin(
            $posts_id
            , \Module\MongoDriver\parseExpressionFromString('stat=publish|draft')
        );

        return $posts;
    }
}
