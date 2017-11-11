<?php
namespace Module\Content\Actions\Comments;

use Module\Content;
use Module\Content\Actions\aAction;
use Module\Content\Interfaces\Model\Repo\iRepoComments;
use Module\Content\Interfaces\Model\Repo\iRepoLikes;
use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;


class AddCommentOnPostAction
    extends aAction
{
    /** @var iRepoLikes */
    protected $repoComments;


    /**
     * Construct
     *
     * @param iHttpRequest  $httpRequest  @IoC /HttpRequest
     * @param iRepoComments $repoComments @IoC /module/content/services/repository/Comments
     */
    function __construct(iHttpRequest $httpRequest, iRepoComments $repoComments)
    {
        parent::__construct($httpRequest);

        $this->repoComments = $repoComments;
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
        /** @var Content\Model\Entity\EntityPost $post */
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
