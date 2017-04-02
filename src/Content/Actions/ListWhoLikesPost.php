<?php
namespace Module\Content\Actions;

use Module\Content\Interfaces\Model\Entity\iEntityLike;
use Module\Content\Interfaces\Model\Repo\iRepoLikes;
use Module\Content\Model\Entity\EntityLike;
use Module\Content\Model\Entity\MemberObject;


class ListWhoLikesPost
{
    /** @var iRepoLikes */
    protected $repoLikes;
    protected $repoPosts;


    /**
     * Construct
     *
     * @param iRepoLikes   $repoLikes @IoC /module/content/services/repository/Likes
     */
    function __construct(iRepoLikes $repoLikes)
    {
        $this->repoLikes = $repoLikes;
    }


    /**
     * List Users who have liked a Post
     *
     * @param string   $content_id Post Content ID
     * @param int|null $skip
     * @param int|null $limit
     *
     * @return \Traversable
     */
    function __invoke($content_id = null, $skip = null, $limit = 30)
    {
        $cursor = $this->repoLikes->findByItemIdentifierOnModel($content_id, EntityLike::MODEL_POSTS, $skip, $limit);
        /** @var iEntityLike $like */
        foreach ($cursor as $like) {
            $member = new MemberObject;
            $member->setUid($like->getOwnerIdentifier());

            yield [
                '$user' => $member,
            ];
        }
    }
}
