<?php
namespace Module\Content\Model\Entity\EntityPost;


class ContentObjectGeneral
    extends ContentObjectPlain
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
     * @param MediaObjectTenderBin $media
     *
     * @return $this
     */
    function addMedia(MediaObjectTenderBin $media)
    {
        $this->medias[] = $media;
        return $this;
    }


    // ...

    /**
     * Build Object With Provided Options
     *
     * @param array $options Associated Array
     * @param bool $throwException Throw Exception On Wrong Option
     *
     * @return array Remained Options (if not throw exception)
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    function with(array $options, $throwException = false)
    {
        if (isset($options['medias']) && $medias = $options['medias']) {
            foreach ($options['medias'] as $i => $media) {
                if (!$media instanceof MediaObjectTenderBin) {
                    // Content Object May Fetch From DB Or Sent By Post Http Request
                    $objectMedia = new MediaObjectTenderBin;
                    $objectMedia->with( $objectMedia::parseWith($media) );
                    $options['medias'][$i] = $objectMedia;
                }
            }
        }


        parent::with($options, $throwException);
    }
}
