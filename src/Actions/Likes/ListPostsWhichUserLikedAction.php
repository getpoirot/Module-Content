<?php
namespace Module\Content\Actions\Likes;

use Module\Content;
use Module\Content\Actions\aAction;
use Module\Content\Interfaces\Model\Repo\iRepoLikes;
use Module\Content\Model\Entity\EntityPost;
use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;
use Module\Content\Events\EventsHeapOfContent;


class ListPostsWhichUserLikedAction
    extends aAction
{
    /** @var iRepoLikes */
    protected $repoLikes;


    /**
     * Construct
     *
     * @param iHttpRequest $httpRequest @IoC /HttpRequest
     * @param iRepoLikes   $repoLikes   @IoC /module/content/services/repository/Likes
     */
    function __construct(iHttpRequest $httpRequest, iRepoLikes $repoLikes)
    {
        parent::__construct($httpRequest);

        $this->repoLikes = $repoLikes;
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

        $q     = ParseRequestData::_($this->request)->parseQueryParams();
        $skip  = (isset($q['skip']))  ? (int) $q['skip']  : null;
        $limit = (isset($q['limit'])) ? (int) $q['limit'] : 30;

        # Retrieve Posts Liked By User
        $crsr = \Module\Content\Actions::ListPostsLikedByUser(
            $token->getOwnerIdentifier()
            , $skip
            , $limit + 1
        );

        ## Retrieve Profiles For Posts Owner
        #
        $posts = [];
        $postOwners = [];
        /** @var EntityPost $post */
        foreach ($crsr as $post) {
            // Create Response Items
            $posts[] = $post;
            $ownerId = (string) $post->getOwnerIdentifier();
            $postOwners[$ownerId] = true;
        }

        $postOwners = array_keys($postOwners);

        $profiles = \Module\Profile\Actions::RetrieveProfiles($postOwners);

        $postsPrepared = [];
        foreach ($posts as $post) {
            $postsPrepared[] = Content\toArrayResponseFromPostEntity($post, $token->getOwnerIdentifier(), $profiles);
        }

        ## Event
        #
        $me = ($token) ? $token->getOwnerIdentifier() : null;
        $postsPrepared = $this->event()
            ->trigger(EventsHeapOfContent::RETRIEVE_POSTS_RESULT, [
                /** @see Content\Events\DataCollector */
                'me' => $me, 'posts' => $postsPrepared
            ])
            ->then(function ($collector) {
                /** @var Content\Events\DataCollector $collector */
                return $collector->getResult();
            });

        // Check whether to display fetch more link in response?
        $linkMore = null;
        if (count($postsPrepared) > $limit) {
            array_pop($postsPrepared);   // skip augmented content to determine has more?
            $linkMore = \Module\HttpFoundation\Actions::url(null);
            $linkMore = (string) $linkMore->uri()->withQuery('skip='.($skip+$limit).'&limit='.$limit);
        }

        return [
            ListenerDispatch::RESULT_DISPATCH => [
                'count' => count($postsPrepared),
                'items' => $postsPrepared,
                '_link_more' => $linkMore,
                '_self' => [
                    'skip'       => $skip,
                    'limit'      => $limit,
                ],
            ],
        ];
    }

}
