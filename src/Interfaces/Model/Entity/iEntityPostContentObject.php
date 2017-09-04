<?php
namespace Module\Content\Interfaces\Model\Entity;

use Poirot\Std\Interfaces\Struct\iValueObject;


interface iEntityPostContentObject
    extends iValueObject
{
    /**
     * Get Content Type
     *
     * @return string
     */
    function getContentType();

    /**
     * Content Field
     *
     * @return iFieldType These can be determine for input fields type
     */
    // function getContentFieldName();
}
