<?php
namespace Module\Content\Actions\Posts;

use Module\Content;
use Poirot\Std\Type\StdArray;
use Module\Content\Actions\aAction;
use Module\Content\Interfaces\Model\Repo\iRepoPosts;
use Module\Content\Model\Entity\EntityPost;
use Module\Profile\Actions\Helpers\RetrieveProfiles;
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
    function __invoke($me = null, $limit = null, $offset = null)
    {
        ## Retrieve All Latest Posts
        #
        $crsr = $this->repoPosts->findAll(
            \Module\MongoDriver\parseExpressionFromString('stat=publish&stat_share=public')
            , $offset
            , $limit
        );


        $posts = [];

        ## Retrieve Profiles For Posts Owner
        #
        $postOwners = [];
        /** @var EntityPost $p */
        foreach ($crsr as $p) {
            $p->setContent(clone $p->getContent());
            array_push($posts, $p);
            $ownerId = (string) $p->getOwnerIdentifier();
            $postOwners[$ownerId] = true;
        }


        $postOwners = array_keys($postOwners);

        /** @var RetrieveProfiles $funListUsers */
        $profiles = \Module\Profile\Actions::RetrieveProfiles($postOwners);

        /** @var EntityPost $post */
        $posts = StdArray::of($posts)->each(function ($post) use ($me, &$profiles) {
            return \Module\Content\toArrayResponseFromPostEntity($post, $me, $profiles);
        })->value;


        ## Event
        #
        /** @var array $posts */
        $posts = $this->event()
            ->trigger(EventsHeapOfContent::LIST_POSTS_RESULT, [
                /** @see Content\Events\DataCollector */
                'me' => $me, 'posts' => $posts
            ])
            ->then(function ($collector) {
                /** @var Content\Events\DataCollector $collector */
                return $collector->getPosts();
            });


        return $posts;
    }
}
