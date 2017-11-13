<?php
namespace Module\Content\Actions\Posts;

use Module\Content;
use Module\Content\Actions\aAction;
use Module\Content\Events\EventsHeapOfContent;
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
     * @param iHttpRequest $httpRequest @IoC /HttpRequest
     * @param iRepoPosts   $repoPosts   @IoC /module/content/services/repository/Posts
     */
    function __construct(iHttpRequest $httpRequest, iRepoPosts $repoPosts)
    {
        parent::__construct($httpRequest);

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
    function __invoke($content_id = null, $token = null)
    {
        # Check Whether Content Post Exists?
        if( false === $post = $this->repoPosts->findOneMatchUid($content_id) )
            throw new Content\Exception\exResourceNotFound(sprintf(
                'Content Post (%s) Not Found.'
                , $content_id
            ));


        # Check User Has Permission On Post

        if ($post->getStat() == Content\Model\Entity\EntityPost::STAT_LOCKED)
            throw new Content\Exception\exPostLocked('Access Denied, Post Is Locked.');


        if (! \Module\Content\Actions::IsUserPermissionOnContent($post, $token) ) {
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


        $me = ($token) ? $token->getOwnerIdentifier() : null;

        ## Event
        #
        /** @var Content\Model\Entity\EntityPost $post */
        $post = $this->event()
            ->trigger(EventsHeapOfContent::RETRIEVE_CONTENT, [
                /** @see Content\Events\DataCollector */
                'me' => $me, 'entity_post' => $post,
            ])
            ->then(function ($collector) {
                /** @var Content\Events\DataCollector $collector */
                return $collector->getEntityPost();
            });



        # Build Response
        #
        $profiles = \Module\Profile\Actions::RetrieveProfiles([$post->getOwnerIdentifier()]);
        $r        = Content\toArrayResponseFromPostEntity($post, $me, $profiles) + [
                '_self' => [
                    'content_id' => $content_id,
                ],
            ];


        ## Event
        #
        /** @var Content\Model\Entity\EntityPost $post */
        $r = $this->event()
            ->trigger(EventsHeapOfContent::RETRIEVE_CONTENT_RESULT, [
                /** @see Content\Events\DataCollector */
                'result' => $r, 'entity_post' => $post, 'me' => $me,
            ])
            ->then(function ($collector) {
                /** @var Content\Events\DataCollector $collector */
                return $collector->getResult();
            });


        return [
            ListenerDispatch::RESULT_DISPATCH => $r
        ];
    }
}
