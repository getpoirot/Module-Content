<?php
namespace Module\Content\Model\Entity\EntityPost;

use Module\Content\Interfaces\Model\Entity\iEntityPostContentObject;
use Module\MongoDriver\Model\aObject;
use Poirot\Std\Struct\aDataOptions;
use Poirot\Std\Struct\DataOptionsOpen;


class ContentObjectPlain
    extends aObject
    implements iEntityPostContentObject
{
    const CONTENT_TYPE = 'plain';

    /** @var string */
    protected $description;


    /**
     * Get Content Type
     *
     * @return string
     */
    function getContentType()
    {
        return static::CONTENT_TYPE;
    }

    /**
     * Set Post Description
     *
     * @param string $text
     *
     * @return $this
     */
    function setDescription($text)
    {
        $this->description = (string) $text;
        return $this;
    }

    /**
     * Get Description
     * @required
     *
     * @return string
     */
    function getDescription()
    {
        return $this->description;
    }
}
