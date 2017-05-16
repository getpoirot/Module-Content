<?php
namespace Module\Content\Actions\Posts;

use Module\Content;
use Module\Content\Actions\aAction;
use Module\Content\Interfaces\Model\Repo\iRepoPosts;
use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Poirot\Application\Exception\exAccessDenied;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;


class RetrievePostAction
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
     * Retrieve Post
     *
     * - only posts with stat publish and share public will attain here.
     * - when user is owner of a post the draft and private posts also can be retrieved.
     * - posts with share:locked is disabled and will not showing in list.
     *
     * @param null                    $content_id
     * @param iAccessToken|null $token
     *
     * @return array
     */
    function __invoke($content_id = null, iAccessToken $token = null)
    {
        # Check Whether Content Post Exists?
        if( false === $post = $this->repoPosts->findOneMatchUid($content_id) )
            throw new Content\Exception\exResourceNotFound(sprintf(
                'Content Post (%s) Not Found.'
                , $content_id
            ));


        # Check User Has Permission On Post

        if ($post->getStat() == Content\Model\Entity\EntityPost::STAT_LOCKED)
            throw new exAccessDenied('Access Denied, Post Is Locked.');

        if (! $this->IsUserPermissionOnContent($post, $token)) {
            // only posts with stat publish and share public will attain here
            if (!
               (  $post->getStat()      == Content\Model\Entity\EntityPost::STAT_PUBLISH
               || $post->getStatShare() == Content\Model\Entity\EntityPost::STAT_SHARE_PUBLIC )
            )
                throw new Content\Exception\exResourceNotFound(sprintf(
                    'Content Post (%s) Not Found.'
                    , $content_id
                ));
        }


        # Build Response

        $me = ($token) ? $token->getOwnerIdentifier() : null;

        return [
            ListenerDispatch::RESULT_DISPATCH =>
                Content\toArrayResponseFromPostEntity($post, $me) + [
                    '_self' => [
                        'content_id' => $content_id,
                    ],
                ]
        ];
    }
}
