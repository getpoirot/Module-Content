<?php
namespace Module\Content
{
    use Module\Content\Model\Entity\EntityPost;
    use Module\Content\Model\Entity\EntityPost\MediaObjectTenderBin;
    use Module\Profile\Actions\Helpers\RetrieveProfiles;
    use Poirot\TenderBinClient;


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



        // TODO embed user detail must done within some attached events

        $uid          = (string) $post->getOwnerIdentifier();
        /** @var RetrieveProfiles $funListUsers */
        $funListUsers = \IOC::GetIoC()->get('/module/profile/actions/ListUsersProfile');
        $user         = $funListUsers([$uid]);
        $user         = $user[$uid];


        #

        return [
            'post' => [
                'uid'        => (string) $post->getUid(),
                'content'    => TenderBinClient\embedLinkToMediaData( $post->getContent() ),
                'stat'       => $post->getStat(),
                'stat_share' => $post->getStatShare(),
                'user'       => $user,
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


    /**
     * Magic Touch Media Contents To Infinite Expiration
     *
     * @param \Traversable $content
     *
     * @throws \Exception
     */
    function assertMediaContents($content)
    {
        if (! $content instanceof \Traversable )
            // Do Nothing!!
            return;


        /** @var TenderBinClient\Client $cTender */
        $cTender = \Module\TenderBinClient\Services::ClientTender();

        foreach ($content as $c)
        {
            if ($c instanceof MediaObjectTenderBin) {
                try {
                    $cTender->touch( $c->getHash() );
                } catch (TenderBinClient\Exceptions\exResourceNotFound $e) {
                    // Specific Content Client Exception
                } catch (\Exception $e) {
                    // Other Errors Throw To Next Layer!
                    throw $e;
                }
            }

            elseif (is_array($c) || $c instanceof \Traversable)
                assertMediaContents($c);
        }
    }
}

namespace Module\Content\Lib
{
    use Module\Content\Exception\exUnknownContentType;
    use Module\Content\Interfaces\Model\Entity\iEntityMediaObject;
    use Module\Content\Interfaces\Model\Entity\iEntityPostContentObject;
    use Module\Content\Model\Entity\EntityPost\MediaObjectTenderBin;
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
            if (! \Module\Content\Services::ContentPlugins()->has($contentName) )
                throw new exUnknownContentType(sprintf(
                    'Content Of Type (%s) Has No Plugin Registered In System.', $contentName
                ));


            /** @var iEntityPostContentObject $contentObject */
            $contentObject = \Module\Content\Services::ContentPlugins()->get($contentName);
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
