<?php
namespace Module\Content\Actions;

use Module\Content\Interfaces\Model\Repo\iRepoPosts;
use Module\Content\Model\Entity\EntityPost;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityAccessToken;


class IsUserPermissionOnContent
{
    /** @var iRepoPosts */
    protected $repoPosts;


    /**
     * Check Whether Current User (By Token) Has Permission On Content?
     *
     *
     * @param EntityPost|null         $post
     * @param iEntityAccessToken|null $token
     *
     * @return bool
     */
    function __invoke(EntityPost $post = null, iEntityAccessToken $token = null)
    {
        $postOwner  = (string) $post->getOwnerIdentifier();
        $tokenOwner = (string) $token->getOwnerIdentifier();

        return $tokenOwner == $postOwner;
    }
}
