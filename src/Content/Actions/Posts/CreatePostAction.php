<?php
namespace Module\Content\Actions\Posts;

use Module\Content;
use Module\Content\Actions\aAction;
use Module\Content\Interfaces\Model\Repo\iRepoPosts;
use Poirot\Application\Sapi\Server\Http\ListenerDispatch;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityAccessToken;


class CreatePostAction
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
     * Create Post By Http Request
     *
     * - Assert Validate Token That Has Bind To ResourceOwner,
     *   Check Scopes
     *
     * - Trigger Create.Post Event To Notify Subscribers
     *
     * @param iEntityAccessToken|null $token
     *
     * @return array
     */
    function __invoke(iEntityAccessToken $token = null)
    {
        # Assert Token
        $this->assertTokenByOwnerAndScope($token);


        # Create Post Entity From Http Request
        $hydratePost = new Content\Model\HydrateEntityPost(
            Content\Model\HydrateEntityPost::parseWith($this->request) );

        $entityPost  = new Content\Model\Entity\EntityPost($hydratePost);

        // Determine Owner Identifier From Token
        $entityPost->setOwnerIdentifier($token->getOwnerIdentifier());


        # Persist Post Entity
        $post = $this->repoPosts->insert($entityPost);


        # Build Response:

        return [
            ListenerDispatch::RESULT_DISPATCH =>
                Content\toArrayResponseFromPostEntity($post)
        ];
    }

    // Helper Action Chains:

}
