<?php
namespace Module\Content\Events;

use Module\Content\Actions\UploadMediaAction;
use Module\Content\Model\Entity\EntityPost;
use Module\TenderBinClient\Model\MediaObjectTenderBinVersions;
use Poirot\TenderBinClient\Model\aMediaObject;
use Poirot\TenderBinClient\Model\MediaObjectTenderBin;


class OnThatEmbedMediaLinks
{
    /**
     * Embed Media Links To Content
     *
     * @param \Traversable $posts
     * @param mixed        $me
     *
     * @return array
     */
    function __invoke($posts, $me)
    {
        /** @var EntityPost $post */
        foreach ($posts as $post)
        {
            $content = $post->getContent();

            if ($content instanceof EntityPost\ContentObjectGeneral) {
                $postMedias = $content->getMedias();

                /** @var aMediaObject $media */
                $medias = [];
                foreach ($postMedias as $media) {
                    if ( \Poirot\Std\isMimeMatchInList(['image/*'], $media->getContentType()) )
                        $media = $this->_mediaVersion($media);

                    $medias[] = $media;
                }


                $content->setMedias($medias);
            }
        }

        return [
            'posts' => $posts,
        ];
    }


    // ..

    /** @see UploadMediaAction */
    private function _mediaVersion(aMediaObject $media)
    {
        $storageType = $media->getStorageType();
        if ( $storageType !== MediaObjectTenderBin::TYPE )
            throw new \RuntimeException(sprintf(
                'Media Object (%s) is unknown for versioned links.'
                , $storageType
            ));


        ## Embed Versions Into Response
        #
        /** @var MediaObjectTenderBin $media */
        return new MediaObjectTenderBinVersions($media, [
            'thumb', 'low_thumb', 'small', 'low_small', 'large', 'low_large', 'origin'
        ]);
    }
}
