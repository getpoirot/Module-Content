<?php
namespace Module\Content\Exception;

use Poirot\Application\Exception\exRouteNotMatch;


class exPostUnavailable
    extends exRouteNotMatch
{
    protected $code = 410;
}
