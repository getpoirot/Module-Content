<?php
namespace Module\Content\Model\Entity\EntityPost;

use Module\MongoDriver\Model\aTypeObject;


class MediaObjectTenderBin
    extends aTypeObject
{
    const TYPE = 'tenderbin';

    /** @var array */
    protected $hash;
    protected $contentType;


    /**
     * Determine Used Stored Engine Type
     *
     * @return string
     */
    final function getStorageType()
    {
        return static::TYPE;
    }


    /**
     * Set BinData Hash Object
     *
     * @param string $hash
     *
     * @return $this
     */
    function setHash($hash)
    {
        $this->hash = (string) $hash;
        return $this;
    }

    function getHash()
    {
        return $this->hash;
    }

    /**
     * Media Content Type (Mime)
     * exp. image/jpeg
     *
     * @param string $contentType
     *
     * @return $this
     */
    function setContentType($contentType)
    {
        $this->contentType = (string) $contentType;
        return $this;
    }

    function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Stream Wrapper Link to Bindata To Retrieve Content
     * note: usually link must provide on-the-fly using media extend
     *
     * @return string|null http://bin/54d3w345
     */
    function get_Link()
    {
        // TODO implement
        return 'http://server/media/'.$this->getHash();
    }
}
