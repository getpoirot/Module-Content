<?php
namespace Module\Content\Actions\Posts;

use Module\Content;
use Module\Content\Actions\aAction;
use Module\Content\Interfaces\Model\Repo\iRepoPosts;
use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\Content\Events\EventsHeapOfContent;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;
use Poirot\Std\Exceptions\exUnexpectedValue;
use Module\Content\Model\PostValidate;


class CreatePostAction
    extends aAction
{
    /** @var iRepoPosts */
    protected $repoPosts;


    /**
     * Construct
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
     * Create Post By Http Request
     *
     * - Assert Validate Token That Has Bind To ResourceOwner,
     *   Check Scopes
     *
     * - Trigger Create.Post Event To Notify Subscribers
     *
     * @param iAccessToken|null $token
     *
     * @return array
     * @throws \Exception
     */
    function __invoke($token = null)
    {
        # Assert Token
        #
        $this->assertTokenByOwnerAndScope($token);


        # Create Post Entity From Http Request
        #
        $hydratePost = new Content\Model\HydrateEntityPost(
            Content\Model\HydrateEntityPost::parseWith($this->request) );



        # Assert Validate Entity
        #
        try
        {
            $entityPost  = new Content\Model\Entity\EntityPost($hydratePost);

            // Determine Owner Identifier From Token
            $entityPost->setOwnerIdentifier($token->getOwnerIdentifier());

            __(new PostValidate($entityPost))
                ->assertValidate();

            // TODO Assert Validate Entity

        } catch (exUnexpectedValue $e)
        {
            // TODO Handle Validation ...
            throw new exUnexpectedValue('Validation Failed', null,  400, $e);
        }

        # Content May Include TenderBin Media
        # so touch-media file for infinite expiration
        #
        $content  = $hydratePost->getContent();
        Content\assertMediaContents($content);

        ## Event
        #
        /** @var Content\Model\Entity\EntityPost $post */
        $entityPost = $this->event()
            ->trigger(EventsHeapOfContent::BEFORE_CREATE_CONTENT, [
                /** @see Content\Events\DataCollector */
                'entity_post' => $entityPost, 'me' => $token->getOwnerIdentifier()
            ])
            ->then(function ($collector) {
                /** @var Content\Events\DataCollector $collector */
                return $collector->getEntityPost();
            });


        # Persist Post Entity
        #
        $post = $this->repoPosts->insert($entityPost);


        # Build Response:
        #
        # TODO: move RetrieveProfiles outside
        $profiles = \Module\Profile\Actions::RetrieveProfiles([ $token->getOwnerIdentifier() ]);
        $r = Content\toArrayResponseFromPostEntity($post, null, $profiles);


        ## Event
        #
        /** @var Content\Model\Entity\EntityPost $post */
        $r = $this->event()
            ->trigger(EventsHeapOfContent::AFTER_CREATE_CONTENT, [
                /** @see Content\Events\DataCollector */
                'result' => $r, 'entity_post' => $post, 'me' => $token->getOwnerIdentifier()
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
