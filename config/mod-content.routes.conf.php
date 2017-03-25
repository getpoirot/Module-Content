<?php
use Poirot\Application\Sapi\Server\Http\ListenerDispatch;

return [
    'content'  => [
        'route' => 'RouteSegment',
        'options' => [
            'criteria'    => '/',
            'match_whole' => false,
        ],
        'params'  => [
            ListenerDispatch::CONF_KEY => [
                // This Action Run First In Chains and Assert Validate Token
                //! define array allow actions on matched routes chained after this action
                /*
                 * [
                 *    [0] => Callable Defined HERE
                 *    [1] => routes defined callable
                 *     ...
                 */
                \Module\OAuth2Client\Actions\IOC::instance()->AssertToken,
            ],
        ],
    ],
];
