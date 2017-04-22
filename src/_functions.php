<?php
namespace Module\Content
{
    use Module\Content\Model\Entity\EntityPost;
    use Module\Content\Model\Entity\MemberObject;

    /**
     * Build Array Response From Given Entity Object
     *
     * @param EntityPost  $post
     * @param null|string $me   Current User Identifier
     *
     * @return array
     */
    function toArrayResponseFromPostEntity(EntityPost $post, $me = null)
    {
        # Build Likes Response:
        $likes = ($post->getLikes()) ? [
            'count'   => $post->getLikes()->getCount(),
            'members' => \Poirot\Std\cast( $post->getLikes()->getLatestMembers() )
                ->withWalk(function(&$value, $key) {
                    $value = ['user' => $value];
                })
        ] : null;

        if ($me && $likes) {
            // Check Whether Current User Has Liked Entity?
            $totalMembers = $post->getLikes()->getTotalMembers();
            if ( in_array((string)$me, $totalMembers) )
                $likes = ['by_me' => true] + $likes;
        }


        #

        return [
            'post' => [
                'uid'        => (string) $post->getUid(),
                'content'    => $post->getContent(),
                'stat'       => $post->getStat(),
                'stat_share' => $post->getStatShare(),
                'user' => new MemberObject( ['uid' => $post->getOwnerIdentifier()] ),
                'location'   => [
                    'caption' => $post->getLocation()->getCaption(),
                    'geo'     => [
                        'lon' => $post->getLocation()->getGeo('lon'),
                        'lat' => $post->getLocation()->getGeo('lat'),
                    ],
                ],
                'likes' => $likes,
                'datetime_created' => [
                    'datetime'  => $post->getDateTimeCreated(),
                    'timestamp' => $post->getDateTimeCreated()->getTimestamp(),
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
            if (! ContentIOC::ContentObjectContainer()->has($contentName) )
                throw new \Exception(sprintf('Content (%s) not registered as plugin.', $contentName));


            $contentObject = ContentIOC::ContentObjectContainer()->get($contentName);
            $contentObject->with($contentObject::parseWith($contentData));

            return $contentObject;
        }
    }
}
