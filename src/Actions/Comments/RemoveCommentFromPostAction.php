<?php
namespace Module\Content\Actions\Comments;

use Module\Content;
use Module\Content\Actions\aAction;
use Module\Content\Interfaces\Model\Repo\iRepoComments;
use Module\Content\Interfaces\Model\Repo\iRepoPosts;
use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Poirot\Application\Exception\exAccessDenied;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;


class RemoveCommentFromPostAction
    extends aAction
{
    /** @var iRepoComments */
    protected $repoComments;
    /** @var iRepoPosts */
    protected $repoPosts;


    /**
     * Construct
     *
     * @param iHttpRequest  $httpRequest  @IoC /HttpRequest
     * @param iRepoComments $repoComments @IoC /module/content/services/repository/Comments
     * @param iRepoPosts    $repoPosts    @IoC /module/content/services/repository/Posts
     */
    function __construct(iHttpRequest $httpRequest, iRepoComments $repoComments, iRepoPosts $repoPosts)
    {
        parent::__construct($httpRequest);

        $this->repoComments = $repoComments;
        $this->repoPosts    = $repoPosts;
    }

    /**
     * Set Like On Post By Authenticated User
     *
     * - Assert Validate Token That Has Bind To ResourceOwner,
     *   Check Scopes
     *
     * - Trigger Like.Post Event To Notify Subscribers
     *
     * @param string       $comment_id
     * @param string       $content_id
     * @param iAccessToken $token
     *
     * @return array
     */
    function __invoke($comment_id = null, $content_id = null, $token = null)
    {
        # Assert Token
        $this->assertTokenByOwnerAndScope($token);


        # Check Whether Current User has Access To Remove Comment
        // check comment owner_id
        if ($comment = $this->repoComments->findOneMatchUid($comment_id))
        {
            if ( (string) $comment->getOwnerIdentifier() === (string) $token->getOwnerIdentifier() )
            {
                // Current User Is Owner Of Comment:
                // so comment will be removed.

                $this->repoComments->remove($comment);
            }
            else
            {
                // Comments From Content Owner; All Soft Deleted!!
                $post = $this->repoPosts->findOneMatchUid($content_id);
                if ( $post && ((string)$post->getOwnerIdentifier() === (string)$token->getOwnerIdentifier()) ) {
                    $this->repoComments->removeSoftly($comment);

                } else
                    // Current User Have Not Access To Remove Comment
                    throw new exAccessDenied('You have not access to remove comment.');
            }
        }

        return [
            ListenerDispatch::RESULT_DISPATCH => [
                'stat' => 'del-comment',
                '_self'   => [
                    'content_id' => $content_id,
                    'comment_id' => $comment_id,
                ],
            ],
        ];
    }

    // Helper Action Chains:

}
