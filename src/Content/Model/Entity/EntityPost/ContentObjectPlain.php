<?php
namespace Module\Content\Model\Entity\EntityPost;

use Module\Content\Interfaces\Model\Entity\iEntityPostContentObject;
use Poirot\Std\Struct\aValueObject;


class ContentObjectPlain
    extends aValueObject
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
