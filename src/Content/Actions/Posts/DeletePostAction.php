<?php
namespace Module\Content\Actions\Posts;

use Module\Content;
use Module\Content\Actions\aAction;
use Module\Content\Interfaces\Model\Repo\iRepoPosts;
use Poirot\Application\Exception\exAccessDenied;
use Poirot\Application\Sapi\Server\Http\ListenerDispatch;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityAccessToken;


class DeletePostAction
    extends aAction
{
    /** @var iRepoPosts */
    protected $repoPosts;


    /**
     *
     * @param iHttpRequest $request   @IoC /
     * @param iRepoPosts   $repoPosts @IoC /module/content/services/repository/Posts
     */
    function __construct(iHttpRequest $request, iRepoPosts $repoPosts)
    {
        parent::__construct($request);

        $this->repoPosts = $repoPosts;
    }


    /**
     * Delete Post By Http Request
     *
     * - Assert Validate Token That Has Bind To ResourceOwner,
     *   Check Scopes
     *
     * - Check User Has Access To Delete Post
     *
     *
     *
     * @param null                    $content_id
     * @param iEntityAccessToken|null $token
     *
     * @return array
     */
    function __invoke($content_id = null, iEntityAccessToken $token = null)
    {
        # Assert Token
        $this->assertTokenByOwnerAndScope($token);


        # Check Whether Content Post Exists?
        if( false === $post = $this->repoPosts->findOneByUID($content_id) )
            throw new Content\Exception\exResourceNotFound(sprintf(
                'Content Post (%s) Not Found.'
                , $content_id
            ));


        # Check User Has Access To Edit Post
        if (! $this->IsUserPermissionOnContent($post, $token))
            throw new exAccessDenied('Don`t Have Permission To Remove Post.');


        # Delete Post

        $isDeleted = $this->repoPosts->deleteOneByUID($post->getUid());


        # Build Response

        return [
            ListenerDispatch::RESULT_DISPATCH => [
                'stat' => 'deleted',
                '_self' => [
                    'content_id' => $content_id,
                ],
            ],
        ];
    }


    // Helper Action Chains:
}
