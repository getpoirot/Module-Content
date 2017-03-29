<?php
namespace Module\Content\Lib;

use Module\Content\Services\IOC as ContentIOC;
use Poirot\Std\Interfaces\Pact\ipFactory;


class FactoryContentObject
    implements ipFactory
{
    /**
     * Factory With Valuable Parameter
     *
     * @param mixed $contentName
     *
     * @throws \Exception
     * @return mixed
     */
    static function of($contentName)
    {
        if (!ContentIOC::ContentObjectContainer()->has($contentName))
            throw new \Exception(sprintf('Content (%s) not registered as plugin.', $contentName));

        $contentObject = ContentIOC::ContentObjectContainer()->get($contentName);
        return $contentObject;
    }
}
