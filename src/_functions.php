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
                'user'       => new MemberObject( ['uid' => $post->getOwnerIdentifier()] ),
                'location'   => ($post->getLocation()) ? [
                    'caption' => $post->getLocation()->getCaption(),
                    'geo'     => [
                        'lon' => $post->getLocation()->getGeo('lon'),
                        'lat' => $post->getLocation()->getGeo('lat'),
                    ],
                ] : null,
                'likes'       => $likes,
                'is_comment_enabled' => $post->getIsCommentEnabled(),
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

    use Module\Content\Exception\exUnknownContentType;
    use Module\Content\Interfaces\Model\Entity\iEntityMediaObject;
    use Module\Content\Interfaces\Model\Entity\iEntityPostContentObject;
    use Module\Content\Model\Entity\EntityPost\MediaObjectTenderBin;
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
                throw new exUnknownContentType(sprintf(
                    'Content Of Type (%s) Has No Plugin Registered In System.', $contentName
                ));


            /** @var iEntityPostContentObject $contentObject */
            $contentObject = ContentIOC::ContentObjectContainer()->get($contentName);
            $contentObject->with($contentObject::parseWith($contentData));

            return $contentObject;
        }
    }


    class FactoryMediaObject
        implements ipFactory
    {
        /**
         * Factory With Valuable Parameter
         *
         * @param null  $mediaData
         *
         * @return iEntityMediaObject
         * @throws \Exception
         */
        static function of($mediaData = null)
        {
            // Content Object May Fetch From DB Or Sent By Post Http Request

            /*
            {
                "storage_type": "tenderbin",
                "hash": "58c7dcb239288f0012569ed0",
                "content_type": "image/jpeg"
            }
            */

            if (! isset($mediaData['storage_type']) )
                $mediaData['storage_type'] = 'tenderbin';

            switch (strtolower($mediaData['storage_type'])) {
                case 'tenderbin':
                    $objectMedia = new MediaObjectTenderBin;
                    $objectMedia->with( $objectMedia::parseWith($mediaData) );
                    break;

                default:
                    throw new \Exception('Object Storage With Name (%s) Is Unknown.');
            }

            return $objectMedia;
        }
    }
}
