<?php
namespace Module\Content
{
    use Module\Content\Model\Entity\EntityPost;
    use Module\Content\Model\Entity\MemberObject;

    /**
     * Build Array Response From Given Entity Object
     *
     * @param EntityPost $post
     *
     * @return array
     */
    function toArrayResponseFromPostEntity(EntityPost $post)
    {
        return [
            '$post' => [
                'uid'        => (string) $post->getUid(),
                'content'    => $post->getContent(),
                'stat'       => $post->getStat(),
                'stat_share' => $post->getStatShare(),
                '$user' => new MemberObject( ['uid' => $post->getOwnerIdentifier()] ),
                '$location'   => [
                    'caption' => $post->getLocation()->getCaption(),
                    'geo'     => [
                        'lon' => $post->getLocation()->getGeo('lon'),
                        'lat' => $post->getLocation()->getGeo('lat'),
                    ],
                ],
                'likes' => ($post->getLikes()) ? [
                    'count'   => $post->getLikes()->getCount(),
                    'members' => \Poirot\Std\cast( $post->getLikes()->getMembers() )
                        ->withWalk(function(&$value, $key) {
                            $value = ['$user' => $value];
                        })
                ] : null,
                'datetime_created' => [
                    '$datetime' => $post->getDateTimeCreated(),
                ],
            ],
        ];
    }
}

namespace Module\Content\Lib
{
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
            $contentObject->with($contentObject::parseWith($contentData));
            if (!$contentObject->isFulfilled())
                throw new \InvalidArgumentException(sprintf(
                    'Content With Type (%s) not Fulfilled with given content.'
                    , $contentName
                ), 400);

            return $contentObject;
        }
    }
}
