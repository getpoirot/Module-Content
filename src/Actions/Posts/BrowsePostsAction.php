<?php
namespace Module\Content\Actions\Posts;

use Module\Content;
use Poirot\Std\Type\StdArray;
use Module\Content\Actions\aAction;
use Module\Content\Model\Entity\EntityPost;
use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\Profile\Actions\Helpers\RetrieveProfiles;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\OAuth2Client\Interfaces\iAccessToken;
use Module\Content\Events\EventsHeapOfContent;


class BrowsePostsAction
    extends aAction
{
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
        ## Assert Token
        #
        $this->assertTokenByOwnerAndScope($token);


        # Parse Request Query params
        $q      = ParseRequestData::_($this->request)->parseQueryParams();
        // offset is Mongo ObjectID "58e107fa6c6b7a00136318e3"
        $offset = (isset($q['offset'])) ? $q['offset']       : null;
        $limit  = (isset($q['limit']))  ? (int) $q['limit']  : 30;


        ## Retrieve Posts
        #
        $me    = ($token) ? $token->getOwnerIdentifier() : null;
        $posts = \Module\Content\Actions::FindLatestPosts($me, $limit+1, $offset);


        ## Build Response
        #
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
