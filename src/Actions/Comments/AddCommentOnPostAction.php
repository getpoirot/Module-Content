<?php
namespace Module\Content\Actions\Comments;

use Module\Content;
use Module\Content\Actions\aAction;
use Module\Content\Interfaces\Model\Repo\iRepoComments;
use Module\Content\Interfaces\Model\Repo\iRepoLikes;
use Module\Content\Interfaces\Model\Repo\iRepoPosts;
use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;


// TODO remove repoPosts and notif action
class AddCommentOnPostAction
    extends aAction
{
    /** @var iRepoLikes */
    protected $repoComments;
    /** @var iRepoPosts */
    protected $repoPosts;


    /**
     * Construct
     *
     * @param iHttpRequest  $httpRequest  @IoC /HttpRequest
     * @param iRepoComments $repoComments @IoC /module/content/services/repository/Comments
     * @param iRepoPosts    $repoPosts    @IoC /module/content/services/repository/Posts
     */
    function __construct(iHttpRequest $httpRequest, iRepoComments $repoComments, iRepoPosts $repoPosts)
    {
        parent::__construct($httpRequest);

        $this->repoComments = $repoComments;
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
     * @param string       $content_id
     * @param iAccessToken $token
     *
     * @return array
     */
    function __invoke($content_id = null, $token = null)
    {
        ## Assert Token
        #
        $this->assertTokenByOwnerAndScope($token);


        ## Validate Comment
        #
        $_posts = ParseRequestData::_($this->request)->parseBody();
        if (!isset($_posts['comment']) || empty($_posts['comment']))
            throw new \InvalidArgumentException('Comment is empty.');


        ## Add Comment To Given Post Content With Id
        #
        $comment = new Content\Model\Entity\EntityComment;
        $comment
            ->setItemIdentifier($content_id)
            ->setOwnerIdentifier( $token->getOwnerIdentifier() )
            ->setContent( $_posts['comment'] )
            ->setModel( Content\Model\Entity\EntityComment::MODEL_POSTS )
        ;


        ## Events
        #
        /** @var Content\Interfaces\Model\Entity\iEntityComment $comment */
        $comment = $this->event()->trigger(
            Content\Events\EventsHeapOfContent::BEFORE_ADD_COMMENT
            , [
                /** @see Content\Events\DataTransferOfComments */
                'comment' => $comment
            ]
        )->then(function ($collector) {
            /** @var Content\Events\DataTransferOfComments $collector */
            return $collector->getComment();
        });


        ## Persist Comment
        #
        $comment = $this->repoComments->insert($comment);



        // TODO with events
        $visitorId = (string) $token->getOwnerIdentifier();
        $profiles  = \Module\Profile\Actions::RetrieveProfiles([$visitorId]);
        $profiles  = $profiles[$visitorId];

        $userName  = (isset($profiles['fullname']))
            ? $profiles['fullname']
            : '@'.$profiles['username'];


        $titlePost = 'شما';
        $post      = $this->repoPosts->findOneMatchUid($content_id);
        $content   = $post->getContent();
        if ( $content  instanceof Content\Model\Entity\EntityPost\ContentObjectGeneral ) {
            $titlePost = $content->getTitle();
            $titlePost = (! empty($titlePost) ) ? $titlePost : 'شما';
        }

        $ownerId = (string) $post->getOwnerIdentifier();

        if ($visitorId !== $ownerId) {
            \Module\Fcm\Actions::SendNotification()
                ->sendSimple(
                    'نظر روی پست شما'
                    , sprintf('%s روی پست %s نظر داده است.', $userName, $titlePost)
                    , [ $ownerId ]
                    , ['entityName' => 'user', 'entityId' =>  $visitorId ]
                );

        }


        ## Build Response
        #
        $commentOwnerId = (string) $comment->getOwnerIdentifier();
        $profiles       = \Module\Profile\Actions::RetrieveProfiles([$commentOwnerId]);

        $result = [
            'uid'     => (string) $comment->getUid(),
            'content' => $comment->getContent(),
            'user'    => $profiles[$commentOwnerId],

            '_self'   => [
                'content_id' => $content_id,
            ],
        ];


        ## Event
        #
        $result = $this->event()
            ->trigger(Content\Events\EventsHeapOfContent::AFTER_ADD_COMMENT, [
                /** @see Content\Events\DataTransferOfComments */
                'result' => $result, 'comment' => $comment,
            ])
            ->then(function ($collector) {
                /** @var Content\Events\DataTransferOfComments $collector */
                return $collector->getResult();
            });


        return [
            ListenerDispatch::RESULT_DISPATCH => $result,
        ];
    }
}
