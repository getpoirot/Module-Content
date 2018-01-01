<?php
namespace Module\Content\Actions\Posts;

use Module\Content;
use Module\Content\Actions\aAction;
use Module\Content\Interfaces\Model\Repo\iRepoPosts;
use Module\Content\Events\EventsHeapOfContent;


class FindLatestPosts
    extends aAction
{
    /** @var iRepoPosts */
    protected $repoPosts;


    /**
     * Construct
     *
     * @param iRepoPosts $repoPosts @IoC /module/content/services/repository/Posts
     */
    function __construct(iRepoPosts $repoPosts)
    {
        $this->repoPosts = $repoPosts;
    }


    /**
     * Latest Posts By Time
     *
     * Search Terms:
     *   Retrieve Specific Post Type
     *   ?content=content_type:general
     *
     * - only public and published post
     * - posts with share:locked is disabled and will not showing in list.
     *
     * @param mixed $me
     * @param int   $limit
     * @param int   $offset
     *
     * @return array
     */
    function __invoke($me = null, $limit = null, $offset = null, array $expression = [])
    {
        $expression = array_merge(
            $expression
            , \Module\MongoDriver\parseExpressionFromString('stat=publish&stat_share=public')
        );


        ## Retrieve All Latest Posts
        #
        $crsr = $this->repoPosts->findAll(
            $expression
            , $offset
            , $limit
        );

        ## Event
        #
        /** @var array $posts */
        $posts = $this->event()
            ->trigger(EventsHeapOfContent::LIST_POSTS_RESULTSET, [
                /** @see Content\Events\DataCollector */
                'me' => $me, 'posts' => $crsr
            ])
            ->then(function ($collector) {
                /** @var Content\Events\DataCollector $collector */
                return $collector->getPosts();
            });


        return $posts;
    }
}
