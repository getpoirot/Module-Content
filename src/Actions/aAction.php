<?php
namespace Module\Content\Actions;

use Module\Content\Events\EventsHeapOfContent;
use Module\Content\Model\Entity\EntityPost;
use Poirot\Application\Exception\exAccessDenied;
use Poirot\Events\Event\BuildEvent;
use Poirot\Events\Event\MeeterIoc;
use Poirot\Events\Interfaces\Respec\iEventProvider;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;


/**
 *
 * @method bool         IsUserPermissionOnContent(EntityPost $post, iAccessToken $token = null)
 * @method \Traversable ListPostsLikedByUser($owner_identifier, $skip = null, $limit = null)
 * @method array        ListPostsOfUser($me, $owner_identifier, $expression = null, $offset = null, $limit = null)
 */
abstract class aAction
    extends \Module\Foundation\Actions\aAction
    implements iEventProvider
{
    const CONF = 'events';

    /** @var iHttpRequest */
    protected $request;
    /** @var EventsHeapOfContent */
    protected $events;

    protected $tokenMustHaveOwner  = true;
    protected $tokenMustHaveScopes = array(

    );


    /**
     * aAction constructor.
     * @param iHttpRequest $httpRequest @IoC /HttpRequest
     */
    function __construct(iHttpRequest $httpRequest)
    {
        $this->request = $httpRequest;
    }


    // Implement Events

    /**
     * Get Events
     *
     * @return EventsHeapOfContent
     */
    function event()
    {
        if (! $this->events ) {
            // Build Events From Merged Config
            $conf   = $this->sapi()->config()->get( \Module\Content\Module::CONF );
            $conf   = $conf[self::CONF];

            $events = new EventsHeapOfContent;
            $builds = new BuildEvent([ 'meeter' => new MeeterIoc, 'events' => $conf ]);
            $builds->build($events);

            $this->events = $events;
        }

        return $this->events;
    }

    // ..

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
