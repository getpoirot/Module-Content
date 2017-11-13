<?php
namespace Module\Content\Exception;


class exPostUnavailable
    extends \RuntimeException
{
    protected $code = 410;
}
