<?php
namespace Module\Content\Actions\Posts;

use Module\Content;
use Module\Content\Actions\aAction;
use Module\Content\Interfaces\Model\Repo\iRepoPosts;
use Poirot\Application\Exception\exAccessDenied;
use Poirot\Application\Sapi\Server\Http\ListenerDispatch;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityAccessToken;


class EditPostAction
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
        if ($post->getStat() == Content\Model\Entity\EntityPost::STAT_LOCKED)
            throw new exAccessDenied('Access Denied, Post Is Locked.');

        if (! $this->IsUserPermissionOnContent($post, $token))
            throw new exAccessDenied('Don`t Have Permission To Edit Post.');


        # Update Post

        // Change Entity From Request
        $postChanged = new Content\Model\HydrateEntityPost($this->request, $post);
        $postChanged->setContentType($post->getContent()->getContentType());
        $post->import($postChanged);

        // Persist Changes
        $post = $this->repoPosts->save($post);


        # Build Response

        return [
            ListenerDispatch::RESULT_DISPATCH =>
                Content\toArrayResponseFromPostEntity($post) + [
                    '_self' => [
                        'content_id' => $content_id,
                    ],
                ]
        ];
    }
}
