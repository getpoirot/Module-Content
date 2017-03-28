<?php
namespace Module\Content\Model\PostContentObject;


use Module\Content\Model\EntityPostMediaObject;

class GeneralContentObject
    extends PlainContentObject
{
    const CONTENT_TYPE = 'general';

    /** @var []EntityPostMediaObject  */
    protected $medias = [];


    /**
     * Set Post Attached Medias
     *
     * @param []EntityPostMediaObject $medias
     *
     * @return $this
     */
    function setMedias(array $medias)
    {
        $this->medias = array();

        foreach ($medias as $m)
            $this->addMedia($m);

        return $this;
    }

    /**
     * Get Attached Medias
     *
     * @return array []EntityPostMediaObject
     */
    function getMedias()
    {
        return $this->medias;
    }

    /**
     * Attach Media To Post
     *
     * @param EntityPostMediaObject $media
     *
     * @return $this
     */
    function addMedia(EntityPostMediaObject $media)
    {
        $this->medias[] = $media;
        return $this;
    }


    // ...

    /**
     * Load Build Options From Given Resource
     *
     * - usually it used in cases that we have to support
     *   more than once configure situation
     *   [code:]
     *     Configurable->with(Configurable::withOf(path\to\file.conf))
     *   [code]
     *
     * !! With this The classes that extend this have to
     *    implement desired parse methods
     *
     * @param array|mixed $optionsResource
     * @param array $_
     *        usually pass as argument into ::with if self instanced
     *
     * @throws \InvalidArgumentException if resource not supported
     * @return array
     */
    static function parseWith($optionsResource, array $_ = null)
    {
        $optionsResource = parent::parseWith($optionsResource, $_);
        if (isset($optionsResource['medias'])) {
            foreach ($optionsResource['medias'] as $i => $media) {
                if (!$media instanceof EntityPostMediaObject) {
                    $mo = new EntityPostMediaObject;
                    $mo->setMediaMeta($media);
                    $optionsResource['medias'][$i] = $mo;
                }
            }
        }

        return $optionsResource;
    }
}
