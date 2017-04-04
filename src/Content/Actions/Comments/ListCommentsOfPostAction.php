<?php
namespace Module\Content\Actions\Comments;

use Module\Content;
use Module\Content\Actions\aAction;
use Module\Content\Interfaces\Model\Repo\iRepoComments;
use Module\Foundation\Actions\IOC;
use Poirot\Application\Sapi\Server\Http\ListenerDispatch;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;


class ListCommentsOfPostAction
    extends aAction
{
    /** @var iRepoComments */
    protected $repoComments;


    /**
     * Construct
     *
     * @param iHttpRequest  $request      @IoC /
     * @param iRepoComments $repoComments @IoC /module/content/services/repository/Comments
     */
    function __construct(iHttpRequest $request, iRepoComments $repoComments)
    {
        parent::__construct($request);

        $this->repoComments = $repoComments;
    }

    /**
     * List Recent Comments on a Post
     *
     * @param string $content_id
     *
     * @return array
     */
    function __invoke($content_id = null)
    {
        $q     = ParseRequestData::_($this->request)->parseQueryParams();
        $skip  = (isset($q['skip']))  ? (int) $q['skip']  : null;
        $limit = (isset($q['limit'])) ? (int) $q['limit'] : 30;


        # Retrieve Comments Of Given Post ID
        $persistComments = $this->repoComments->findByItemIdentifierOfModel(
            $content_id
            , Content\Model\Entity\EntityComment::MODEL_POSTS
            , $skip
            , $limit
        );

        $comments  = [];
        /** @var Content\Model\Entity\EntityComment $comment */
        foreach ($persistComments as $comment) {
            $cid = (string) $comment->getUid();
            $comments[ $cid ] = [
                'uid'     => $cid,
                'content' => $comment->getContent(),
                'user' => new Content\Model\Entity\MemberObject([
                    'uid' => $comment->getOwnerIdentifier(),
                ])
            ];
        }


        # Build Response:

        // Check whether to display fetch more link in response?
        $linkMore = null;
        if (count($comments) >= $limit) {
            $linkMore = IOC::url(null, array('content_id' => $content_id));
            $linkMore = (string) $linkMore->uri()->withQuery('skip='.($skip+$limit).'&limit='.$limit);
        }

        return [
            ListenerDispatch::RESULT_DISPATCH => [
                'count' => count($comments),
                'items' => array_values($comments),
                '_link_more' => $linkMore,
                '_self' => [
                    'content_id' => $content_id,
                    'skip'       => $skip,
                    'limit'      => $limit,
                ],
            ],
        ];
    }

    // Helper Action Chains:

}
