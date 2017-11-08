<?php
namespace Module\Content\Actions\Posts;

use Module\Content;
use Module\Content\Actions\aAction;
use Module\Content\Interfaces\Model\Repo\iRepoPosts;
use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;


class LockPostAction
    extends aAction
{
    /** @var iRepoPosts */
    protected $repoPosts;

    protected $tokenMustHaveOwner = false;
    protected $tokenMustHaveScopes = array(

    );

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
     * @param iAccessToken|null $token
     *
     * @return array
     */
    function __invoke($content_id = null, $token = null)
    {
        # Assert Token
        #
        $this->assertTokenByOwnerAndScope($token);


        # Check Whether Content Post Exists?
        #
        if( false === $post = $this->repoPosts->findOneMatchUid($content_id) )
            throw new Content\Exception\exResourceNotFound(sprintf(
                'Content Post (%s) Not Found.'
                , $content_id
            ));


        $isLocked = $this->repoPosts->lockOneMatchUid($post->getUid());


        # Build Response

        return [
            ListenerDispatch::RESULT_DISPATCH => [
                'stat' => 'locked',
                '_self' => [
                    'post_id' => $content_id,
                ],
            ],
        ];
    }
}
