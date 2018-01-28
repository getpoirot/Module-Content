<?php
namespace Module\Content\Exception;


class exPostLocked
    extends exPostUnavailable
{
    protected $code = 423;
}
