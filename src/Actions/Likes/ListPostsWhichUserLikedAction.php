<?php
namespace Module\Content\Actions\Likes;

use Module\Content;
use Module\Content\Actions\aAction;
use Module\Content\Interfaces\Model\Repo\iRepoLikes;
use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;


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
    function __invoke(iAccessToken $token = null)
    {
        # Assert Token
        $this->assertTokenByOwnerAndScope($token);

        $q     = ParseRequestData::_($this->request)->parseQueryParams();
        $skip  = (isset($q['skip']))  ? (int) $q['skip']  : null;
        $limit = (isset($q['limit'])) ? (int) $q['limit'] : 30;

        # Retrieve Posts Liked By User
        $posts = $this->ListPostsLikedByUser(
            $token->getOwnerIdentifier()
            , $skip
            , $limit + 1
        );

        $postsPrepared = [];
        foreach ($posts as $post) {
            // Create Response Items
            $postsPrepared[] = Content\toArrayResponseFromPostEntity( $post, $token->getOwnerIdentifier() );
        }

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

    // Helper Action Chains:

}
