<?php
namespace Module\Content\ActionsHelper;

use Module\Content\Interfaces\Model\Repo\iRepoPosts;
use Module\Content\Model\Entity\EntityPost;


class FindPostsWithIds
{
    /** @var iRepoPosts */
    protected $repoPosts;


    /**
     * Construct
     *
     * @param iRepoPosts $repoPosts  @IoC /module/content/services/repository/Posts
     */
    function __construct(
        iRepoPosts $repoPosts
    ) {
        $this->repoPosts = $repoPosts;
    }


    function __invoke(array $postIds, $expression = null)
    {
        $crsr = $this->repoPosts->findAllMatchUidWithin(
            $postIds,
            $expression
        );


        ## Retrieve Profiles For Posts Owners
        #
        $posts = [];
        /** @var EntityPost $post */
        foreach ($crsr as $post)
           $posts[(string)$post->getUid()] = $post;


        ## Re-Order Posts based on their Score
        #
        $r = [];
        foreach ($postIds as $pid) {
            if (! isset($posts[$pid]) )
                // Maybe post id that given has no match on expression.
                continue;

            $r[] = $posts[$pid];
        }

        return $r;
    }
}
