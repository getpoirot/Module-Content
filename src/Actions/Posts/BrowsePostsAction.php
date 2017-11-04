<?php
namespace Module\Content\Actions\Posts;

use Module\Content;
use Module\Content\Actions\aAction;
use Module\Content\Interfaces\Model\Repo\iRepoPosts;
use Module\Content\Model\Entity\EntityPost;
use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\Profile\Actions\Helpers\RetrieveProfiles;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;
use Poirot\Std\Type\StdArray;
use Poirot\Std\Type\StdTravers;
use Module\Content\Events\EventsHeapOfContent;


class BrowsePostsAction
    extends aAction
{
    /** @var iRepoPosts */
    protected $repoPosts;


    /**
     *
     * @param iHttpRequest $httpRequest @IoC /HttpRequest
     * @param iRepoPosts   $repoPosts   @IoC /module/content/services/repository/Posts
     */
    function __construct(iHttpRequest $httpRequest, iRepoPosts $repoPosts)
    {
        parent::__construct($httpRequest);

        $this->repoPosts = $repoPosts;
    }


    /**
     * Suggest user posts stream to explore
     *
     * Search Terms:
     *   Retrieve Specific Post Type
     *   ?content=content_type:general
     *
     * - only public and published post
     * - posts with share:locked is disabled and will not showing in list.
     *
     * @param iAccessToken|null $token
     *
     * @return array
     * @throws \Exception
     */
    function __invoke($token = null)
    {
        # Assert Token
        $this->assertTokenByOwnerAndScope($token);


        # Parse Request Query params
        $q      = ParseRequestData::_($this->request)->parseQueryParams();
        // offset is Mongo ObjectID "58e107fa6c6b7a00136318e3"
        $offset = (isset($q['offset'])) ? $q['offset']       : null;
        $limit  = (isset($q['limit']))  ? (int) $q['limit']  : 30;


        # Retrieve All Latest Posts
        $crsr = $this->repoPosts->findAll(
            \Module\MongoDriver\parseExpressionFromString('stat=publish&stat_share=public')
            , $offset
            , $limit + 1
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
        $posts = StdArray::of($posts)->each(function ($post) use ($token, &$profiles) {
            return \Module\Content\toArrayResponseFromPostEntity($post, $token->getOwnerIdentifier(), $profiles);
        })->value;

        ## Event
        #
        $me = ($token) ? $token->getOwnerIdentifier() : null;
        $posts = $this->event()
            ->trigger(EventsHeapOfContent::LIST_POSTS_RESULT, [
                /** @see Content\Events\DataCollector */
                'me' => $me, 'posts' => $posts
            ])
            ->then(function ($collector) {
                /** @var Content\Events\DataCollector $collector */
                return $collector->getPosts();
            });

        // Check whether to display fetch more link in response?
        $linkMore = null;
        if (count($posts) > $limit) {
            array_pop($posts);                     // skip augmented content to determine has more?
            $nextOffset = $posts[count($posts)-1]; // retrieve the next from this offset (less than this)
            $linkMore   = \Module\HttpFoundation\Actions::url(null);
            $linkMore   = (string) $linkMore->uri()->withQuery('offset='.($nextOffset['uid']).'&limit='.$limit);
        }

        return [
            ListenerDispatch::RESULT_DISPATCH => [
                'count' => count($posts),
                'items' => $posts,
                '_link_more' => $linkMore,
                '_self' => [
                    'offset' => $offset,
                    'limit'  => $limit,
                ],
            ],
        ];
    }
}
