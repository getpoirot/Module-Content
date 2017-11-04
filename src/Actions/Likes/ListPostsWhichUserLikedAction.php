<?php
namespace Module\Content\Actions\Likes;

use Module\Content;
use Module\Content\Actions\aAction;
use Module\Content\Interfaces\Model\Repo\iRepoLikes;
use Module\Content\Interfaces\Model\Repo\iRepoPosts;
use Module\Content\Model\Entity\EntityLike;
use Module\Content\Model\Entity\EntityPost;
use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use MongoDB\Driver\Cursor;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;


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
        $postCrsr = $this->repoPosts->findAllMatchUidWithin(
            $postsFromLiked
            , 'stat=publish'
        );


        ## Retrieve Profiles For Posts Owner
        #
        $posts = [];
        $postOwners = [];
        /** @var EntityPost $post */
        foreach ($postCrsr as $post) {
            // Create Response Items
            $posts[] = $post;

            $ownerId              = (string) $post->getOwnerIdentifier();
            $postOwners[$ownerId] = true;
        }

        $postOwners = array_keys($postOwners);
        $profiles   = \Module\Profile\Actions::RetrieveProfiles($postOwners);

        $postsPrepared = [];
        foreach ($posts as $post) {
            $postId                 = (string) $post->getUid();
            $postsPrepared[$postId] = Content\toArrayResponseFromPostEntity($post, $token->getOwnerIdentifier(), $profiles);
        }


        // Check whether to display fetch more link in response?
        $linkMore = null;
        if ($count > $limit) {
            $linkMore = \Module\HttpFoundation\Actions::url(null);
            $linkMore = (string) $linkMore->uri()->withQuery('offset='.$next.'&limit='.$limit);
        }

        return [
            ListenerDispatch::RESULT_DISPATCH => [
                'count'      => count($postsPrepared),
                'items'      => array_values($postsPrepared),
                '_link_more' => $linkMore,
                '_self' => [
                    'offset'     => $offset,
                    'limit'      => $limit,
                ],
            ],
        ];
    }

}
