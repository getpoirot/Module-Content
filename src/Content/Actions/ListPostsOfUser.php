<?php
namespace Module\Content\Actions;

use Module\Content\Interfaces\Model\Repo\iRepoPosts;
use Module\Content\Model\Entity\EntityPost;


class ListPostsOfUser
{
    protected $repoPosts;


    /**
     * Construct
     *
     * @param iRepoPosts   $repoPosts @IoC /module/content/services/repository/Posts
     */
    function __construct(iRepoPosts $repoPosts)
    {
        $this->repoPosts = $repoPosts;
    }


    /**
     * Retrieve Displayable Posts Of a User
     *
     * @param string   $owner_identifier Owner Identifier
     * @param array    $expression       Filter expression
     * @param int|null $offset
     * @param int|null $limit
     *
     * @return array
     */
    function __invoke($owner_identifier = null, $expression = null, $offset = null, $limit = 30)
    {
        if (!$expression)
            $expression = \Module\MongoDriver\parseExpressionFromString('stat=publish|draft');

        $persistPosts = $this->repoPosts->findAllMatchWithOwnerId(
            $owner_identifier
            , $expression
            , $offset
            , $limit
        );

        /** @var EntityPost $post */
        $posts = \Poirot\Std\cast($persistPosts)->toArray(function (&$post) {
            $post = \Module\Content\toArrayResponseFromPostEntity($post);
        });

        return $posts;
    }
}
