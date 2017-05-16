<?php
namespace Module\Content\Exception;

use Poirot\Application\Exception\exRouteNotMatch;


class exResourceNotFound
    extends exRouteNotMatch
{
    protected $code = 404;
}
