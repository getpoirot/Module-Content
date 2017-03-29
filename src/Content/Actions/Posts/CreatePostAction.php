<?php
namespace Module\Content\Actions\Posts;

use Module\Content;
use Module\Content\Actions\aAction;
use Module\Content\Interfaces\Model\Repo\iRepoPosts;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityAccessToken;


class CreatePostAction
    extends aAction
{
    /** @var iRepoPosts */
    protected $repoPosts;

    
    /**
     * Create Post By Http Request
     * 
     * @param iHttpRequest|null       $request
     * @param iEntityAccessToken|null $token
     */
    function __invoke(iHttpRequest $request = null, iEntityAccessToken $token = null)
    {
        if (!$request instanceof iHttpRequest)
            throw new \RuntimeException('Cant attain Http Request Object.');


        # Validate Access Token
        \Module\OAuth2Client\validateGivenToken($token, (object) ['mustHaveOwner' => true, 'scopes' => [] ]);


        $entityPost = new Content\Model\Entity\EntityPost(
            new Content\Model\HydrateEntityPostFromRequest($request)
        );

        $persistPost = $this->CreatePost($entityPost);

        print_r($persistPost->getUid());die;
    }
}
