<?php
namespace Module\Content\Actions\Posts;

use Module\Content;
use Module\Content\Actions\aAction;
use Module\Content\Interfaces\Model\Repo\iRepoPosts;
use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;
use Poirot\Std\Exceptions\exUnexpectedValue;
use Poirot\TenderBinClient\Client;


class CreatePostAction
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
     */
    function __invoke(iAccessToken $token = null)
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

            // TODO Assert Validate Entity

        } catch (exUnexpectedValue $e)
        {
            // TODO Handle Validation ...
            throw new exUnexpectedValue('Validation Failed', null,  400, $e);
        }
        catch (\Exception $e) {
            throw $e;
        }


        # Content May Include TenderBin Media
        # so touch-media file for infinite expiration
        #
        $content  = $hydratePost->getContent();

        $_f_touch = function ($traversable) use (&$_f_touch)
        {
            /** @var Client $cTender */
            $cTender = \Module\Content\Services\IOC::ClientTender();

            foreach ($traversable as $c)
            {
                if ($c instanceof Content\Model\Entity\EntityPost\MediaObjectTenderBin) {
                    try {
                        $cTender->touch( $c->getHash() );
                    } catch (\Exception $e) {
                        // TODO Maybe File Hash Not Found or something ...
                    }
                }

                elseif (is_array($c) || $c instanceof \Traversable)
                    $_f_touch($c);
            }
        };

        $_f_touch($content);


        # Persist Post Entity
        #
        $post = $this->repoPosts->insert($entityPost);


        # Build Response:
        #
        return [
            ListenerDispatch::RESULT_DISPATCH =>
                Content\toArrayResponseFromPostEntity($post)
        ];
    }

    // Helper Action Chains:

}
