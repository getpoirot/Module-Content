<?php
namespace Module\Content\Actions\Posts;

use Module\Content;
use Module\Content\Actions\aAction;
use Module\Content\Interfaces\Model\Repo\iRepoPosts;
use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Poirot\Application\Exception\exAccessDenied;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;


class EditPostAction
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
     * Edit Post By Http Request
     *
     * - Assert Validate Token That Has Bind To ResourceOwner,
     *   Check Scopes
     *
     * - Check User Has Access To Edit Post
     *
     *
     *
     * @param null                    $content_id
     * @param iAccessToken|null $token
     *
     * @return array
     */
    function __invoke($content_id = null, iAccessToken $token = null)
    {
        # Assert Token
        $this->assertTokenByOwnerAndScope($token);


        # Check Whether Content Post Exists?
        if( false === $post = $this->repoPosts->findOneMatchUid($content_id) )
            throw new Content\Exception\exResourceNotFound(sprintf(
                'Content Post (%s) Not Found.'
                , $content_id
            ));


        # Check User Has Access To Edit Post
        if ($post->getStat() == Content\Model\Entity\EntityPost::STAT_LOCKED)
            throw new exAccessDenied('Access Denied, Post Is Locked.');

        if (! $this->IsUserPermissionOnContent($post, $token))
            throw new exAccessDenied('Don`t Have Permission To Edit Post.');


        # Update Post

        # Create Post Entity From Http Request
        #
        $hydratePost = new Content\Model\HydrateEntityPost(
            Content\Model\HydrateEntityPost::parseWith($this->request) );


        # Content May Include TenderBin Media
        # so touch-media file for infinite expiration
        #
        $content  = $hydratePost->getContent();
        Content\assertMediaContents($content);

        // Content Type May Not Changed!!
        $hydratePost->setContentType($post->getContent()->getContentType());

        // Change Entity From Request
        $postChanged = $post;
        $postChanged->import($hydratePost);


        // Persist Changes
        $post = $this->repoPosts->save($post);


        # Build Response

        return [
            ListenerDispatch::RESULT_DISPATCH =>
                Content\toArrayResponseFromPostEntity($post, $token->getOwnerIdentifier()) + [
                    '_self' => [
                        'content_id' => $content_id,
                    ],
                ]
        ];
    }
}
