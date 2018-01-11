<?php
namespace Module\Content
{

    use Poirot\Std\Struct\DataEntity;
    use Poirot\TenderBinClient;
    use Module\Content\Model\Entity\EntityPost;


    /**
     * Build Array Response From Given Entity Object
     *
     * @param EntityPost  $post
     * @param null|string $me       Current User Identifier
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


        $user = [
            'uid' => $post->getOwnerIdentifier(), ];


        return [
            'uid'        => (string) $post->getUid(),
            'content'    => TenderBinClient\embedLinkToMediaData($post->getContent()),
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


        foreach ($content as $c) {
            if ($c instanceof TenderBinClient\Model\aMediaObject) {

                $handler = TenderBinClient\FactoryMediaObject::hasHandlerOfStorage($c->getStorageType());

                try {
                    if ($handler)
                    {
                        // Touch Media Bin And Assert Content/Meta
                        //
                        /** @var DataEntity $r */
                        $r = $handler->client()->touch( $c->getHash() );
                        $r = $r->get('result');

                        $meta        = $r['bindata']['meta'];
                        $contentType = $r['bindata']['content_type'];

                        $c->setMeta($meta);
                        $c->setContentType($contentType);


                        // Set Versions Available For This Media
                        //
                        $r = $handler->client()->getBinMeta($c->getHash());

                        if (isset($r['versions']) && !empty($r['versions'])) {
                            $versions = [];
                            foreach ($r['versions'] as $vname => $values) {
                                $uid = $values['bindata']['uid'];
                                $versions[$vname] = $uid;
                            }

                            $r = $handler->client()->listBinMeta( array_values($versions) );

                            // append to media object as version
                            $storageType = $c->getStorageType();
                            foreach ($versions as $vname => $hash)
                            {
                                $subVer = TenderBinClient\FactoryMediaObject::of(null, $storageType);
                                $subVer->setHash($hash);
                                $subVer->setMeta( $r[$hash] );

                                $c->addVersion(
                                    $vname
                                    , $subVer
                                );

                            }
                        }
                    }

                } catch (TenderBinClient\Exceptions\exResourceNotFound $e) {
                    // Specific Content Client Exception
                    throw $e;

                } catch (\Exception $e) {
                    // Other Errors Throw To Next Layer!
                    throw $e;
                }

            } elseif (is_array($c) || $c instanceof \Traversable) {
                assertMediaContents($c);
            }
        }
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
         * @return iEntityPostContentObject
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
