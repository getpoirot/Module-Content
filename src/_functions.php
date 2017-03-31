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
     * @param null  $contentData
     *
     * @return mixed
     * @throws \Exception
     */
    static function of($contentName, $contentData = null)
    {
        if (!ContentIOC::ContentObjectContainer()->has($contentName))
            throw new \Exception(sprintf('Content (%s) not registered as plugin.', $contentName));


        $contentObject = ContentIOC::ContentObjectContainer()->get($contentName);
        if ($contentData !== null) {
            $contentObject->with($contentObject::parseWith($contentData));
            if (!$contentObject->isFulfilled())
                throw new \InvalidArgumentException(sprintf(
                    'Content With Type (%s) not Fulfilled with given content.'
                    , $contentName
                ), 400);
        }

        return $contentObject;
    }
}

