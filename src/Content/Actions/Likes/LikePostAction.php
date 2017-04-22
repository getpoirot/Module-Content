<?php
namespace Module\Content\Actions\Likes;

use Module\Content;
use Module\Content\Actions\aAction;
use Module\Content\Interfaces\Model\Repo\iRepoLikes;
use Module\Content\Interfaces\Model\Repo\iRepoPosts;
use Module\Content\Model\Entity\EntityLike;
use Module\Content\Model\Entity\MemberObject;
use Poirot\Application\Sapi\Server\Http\ListenerDispatch;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityAccessToken;


class LikePostAction
    extends aAction
{
    /** @var iRepoLikes */
    protected $repoLikes;
    /** @var iRepoPosts */
    protected $repoPosts;


    /**
     * Construct
     *
     * @param iHttpRequest $request   @IoC /
     * @param iRepoLikes   $repoLikes @IoC /module/content/services/repository/Likes
     * @param iRepoPosts   $repoPosts @IoC /module/content/services/repository/Posts
     */
    function __construct(iHttpRequest $request, iRepoLikes $repoLikes, iRepoPosts $repoPosts)
    {
        parent::__construct($request);

        $this->repoLikes = $repoLikes;
        $this->repoPosts = $repoPosts;
    }

    /**
     * Set Like On Post By Authenticated User
     *
     * - Assert Validate Token That Has Bind To ResourceOwner,
     *   Check Scopes
     *
     * - Trigger Like.Post Event To Notify Subscribers
     *
     * @param string             $content_id
     * @param iEntityAccessToken $token
     *
     * @return array
     */
    function __invoke($content_id = null, iEntityAccessToken $token = null)
    {
        # Assert Token
        $this->assertTokenByOwnerAndScope($token);


        # Persist Like Entity
        $like = new EntityLike;
        $like
            ->setOwnerIdentifier($token->getOwnerIdentifier())
            ->setItemIdentifier($content_id)
            ->setModel(EntityLike::MODEL_POSTS)
        ;


        $objMember = new MemberObject;
        $objMember->setUid($token->getOwnerIdentifier());

        $postEntity = null;
        if ( $this->repoLikes->save($like) ) {
            # Embed Like Into Post Document
            $likes = $this->repoPosts->insertLikeEntry($content_id, $objMember);
        }


        # Build Response:

        if ( isset($likes) ) {
            $r = [
                'stat'  => 'like',
                'count' => $likes->getCount(),
            ];
        } else {
            $r = [
                'stat' => 'none',
            ];
        }

        return [
            ListenerDispatch::RESULT_DISPATCH => $r + [
                'user'  => $objMember,
                '_self' => [
                    'content_id' => $content_id
                ],
            ],
        ];
    }

    // Helper Action Chains:

}
