<?php
namespace Module\Content
{
    use Poirot\Std\Type\StdArray;
    use Poirot\Std\Type\StdTravers;
    use Poirot\TenderBinClient;
    use Module\Content\Model\Entity\EntityPost;
    use Poirot\TenderBinClient\Model\MediaObjectTenderBin;


    /**
     * Build Array Response From Given Entity Object
     *
     * @param EntityPost  $post
     * @param null|string $me       Current User Identifier
     * @param array       $profiles Default Users Profile Data
     *
     * @return array
     */
    function toArrayResponseFromPostEntity(EntityPost $post, $me = null, array $profiles = [])
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
        // TODO remove dependency (call with service)
        $uid  = (string) $post->getOwnerIdentifier();
        $user = [
            'uid'    => $uid, ];

        if (isset($profiles[$uid]) )
            $user = $profiles[$uid];


        ##

        return [
            'uid'        => (string) $post->getUid(),
            'content'    => embedLinkToMediaData( $post->getContent() ),
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
        if (!($content instanceof \Traversable || is_array($content)))
            // Do Nothing!!
            return;


        /** @var TenderBinClient\Client $cTender */
        $cTender = \Module\TenderBinClient\Services::ClientTender();
        foreach ($content as $c) {
            if ($c instanceof MediaObjectTenderBin) {
                try {
                    $cTender->touch($c->getHash());

                } catch (TenderBinClient\Exceptions\exResourceNotFound $e) {
                    // Specific Content Client Exception
                } catch (\Exception $e) {
                    // Other Errors Throw To Next Layer!
                    throw $e;
                }
            } elseif (is_array($c) || $c instanceof \Traversable) {
                assertMediaContents($c);
            }
        }
    }


    function embedLinkToMediaData($content)
    {
        if ($content instanceof \Traversable )
            $content = StdTravers::of($content)->toArray();

        if (! is_array($content) )
            throw new \Exception('Medias is not an type of array.');


        $content = StdArray::of($content)->withWalk(function(&$val) {
            if (!$val instanceof MediaObjectTenderBin )
                return;

            $orig         = $val;
            $link = (string) \Module\Foundation\Actions::Path(
                'tenderbin-media_cdn' // this name is reserved; @see mod-content.conf.php
                , [
                    'hash' => $orig->getHash()
                ]
            );

            $val          = StdTravers::of($val)->toArray();
            /*
            $val['_link'] = [
                'thumb'      => $link.'?ver=thumb',
                'low_thumb'  => $link.'?ver=low_thumb',
                'small'      => $link.'?ver=small',
                'low_small'  => $link.'?ver=low_small',
                'large'      => $link.'?ver=large',
                'low_large'  => $link.'?ver=low_large',
                'origin' => $link,
            ];
            */
            $val['_link'] = [
                'thumb'      => 'http://optimizer.'.SERVER_NAME.'/?type=crop&size=200x200&url='.$link.'/file.jpg',
                'low_thumb'  => null,
                'small'      => 'http://optimizer.'.SERVER_NAME.'/?type=crop&size=400x400&url='.$link.'/file.jpg',
                'low_small'  => null,
                'large'      => 'http://optimizer.'.SERVER_NAME.'/?type=resize&size=800x1400&url='.$link.'/file.jpg',
                'low_large'  => null,
                'origin'     => $link,
            ];
        });

        return $content->value; // instance access to internal array
    }
}

namespace Module\Content\Lib
{
    use Module\Content\Exception\exUnknownContentType;
    use Module\Content\Interfaces\Model\Entity\iEntityPostContentObject;
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
            $contentObject = \Module\Content\Services::ContentPlugins()->fresh($contentName);
            $contentObject->with($contentObject::parseWith($contentData));
            return $contentObject;
        }
    }
}
