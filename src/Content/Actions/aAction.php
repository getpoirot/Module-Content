<?php
namespace Module\Content\Actions;


use Module\Content\Model\Entity\EntityPost;
use Poirot\Application\Exception\exAccessDenied;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;


/**
 *
 * @method bool         IsUserPermissionOnContent(EntityPost $post, iAccessToken $token = null)
 * @method \Traversable ListPostsLikedByUser($owner_identifier, $skip = null, $limit = null)
 * @method array        ListPostsOfUser($owner_identifier, $expression = null, $offset = null, $limit = null)
 */
abstract class aAction
    extends \Module\Foundation\Actions\aAction
{
    /** @var iHttpRequest */
    protected $request;

    protected $tokenMustHaveOwner  = true;
    protected $tokenMustHaveScopes = array(

    );


    /**
     * aAction constructor.
     * @param iHttpRequest $request @IoC /
     */
    function __construct(iHttpRequest $request)
    {
        $this->request = $request;
    }


    /**
     * Assert Token
     *
     * @param iAccessToken $token
     *
     * @throws exAccessDenied
     */
    protected function assertTokenByOwnerAndScope($token)
    {
        # Validate Access Token
        \Module\OAuth2Client\Assertion\validateAccessToken(
            $token
            , (object) ['mustHaveOwner' => $this->tokenMustHaveOwner, 'scopes' => $this->tokenMustHaveScopes ]
        );

    }
}
