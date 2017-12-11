<?php
namespace Module\Content\Actions\Likes;

use Module\Content;
use MongoDB\Driver\Cursor;
use Module\Content\Actions\aAction;
use Module\Content\Interfaces\Model\Repo\iRepoLikes;
use Module\Content\Interfaces\Model\Repo\iRepoPosts;
use Module\Content\Model\Entity\EntityLike;
use Module\Content\Model\Entity\EntityPost;
use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;
use Module\Content\Events\EventsHeapOfContent;
use Poirot\Std\Type\StdArray;


class ListPostsWhichUserLikedAction
    extends aAction
{
    /** @var iRepoLikes */
    protected $repoLikes;
    protected $repoPosts;


    /**
     * Construct
     *
     * @param iHttpRequest $httpRequest @IoC /HttpRequest
     * @param iRepoLikes   $repoLikes   @IoC /module/content/services/repository/Likes
     * @param iRepoPosts   $repoPosts   @IoC /module/content/services/repository/Posts
     */
    function __construct(iHttpRequest $httpRequest, iRepoLikes $repoLikes, iRepoPosts $repoPosts)
    {
        parent::__construct($httpRequest);

        $this->repoLikes = $repoLikes;
        $this->repoPosts = $repoPosts;
    }

    /**
     * Get the list of posts liked by the current user.
     *
     * @param iAccessToken $token
     *
     * @return array
     */
    function __invoke($token = null)
    {
        # Assert Token
        $this->assertTokenByOwnerAndScope($token);

        $q      = ParseRequestData::_($this->request)->parseQueryParams();
        $offset = (isset($q['offset'])) ? $q['offset']       : null;
        $limit  = (isset($q['limit']))  ? (int) $q['limit']  : 30;


        ## Retrieve Posts Liked By User
        #
        /** @var Cursor $likedPost */
        $likedPost = \Module\Content\Actions::ListPostsLikedByUser(
            $token->getOwnerIdentifier()
            , $offset
            , $limit + 1
        );


        $next = null;
        /** @var EntityLike $like */
        $postsFromLiked = [];
        foreach ($likedPost as $like) {
            $postsFromLiked[] = (string) $like->getItemIdentifier();
            $next             = (string) $like->getIdentifier();
        }

        $count = count($postsFromLiked);

        // ignore one more limit to detect has next page
        array_pop($postsFromLiked);

        $posts = \Module\Content\Actions::FindPostsWithIds(
            $postsFromLiked,
            \Module\MongoDriver\parseExpressionFromString('stat=publish&stat_share=public')
        );


        ## Event
        #
        $me = ($token) ? $token->getOwnerIdentifier() : null;
        $posts = $this->event()
            ->trigger(EventsHeapOfContent::LIST_POSTS_RESULTSET, [
                /** @see Content\Events\DataCollector */
                'me' => $me, 'posts' => $posts
            ])
            ->then(function ($collector) {
                /** @var Content\Events\DataCollector $collector */
                return $collector->getPosts();
            });


        // Check whether to display fetch more link in response?
        $linkMore = null;
        if ($count > $limit) {
            $linkMore = \Module\HttpFoundation\Actions::url(null);
            $linkMore = (string) $linkMore->uri()->withQuery('offset='.$next.'&limit='.$limit);
        }

        return [
            ListenerDispatch::RESULT_DISPATCH => [
                'count'      => count($postsFromLiked),
                'items'      => $posts,
                '_link_more' => $linkMore,
                '_self' => [
                    'offset'     => $offset,
                    'limit'      => $limit,
                ],
            ],
        ];
    }
}
