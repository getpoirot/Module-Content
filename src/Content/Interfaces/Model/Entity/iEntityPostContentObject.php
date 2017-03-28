<?php
namespace Module\Content\Interfaces\Model\Entity;

use Poirot\Std\Interfaces\Pact\ipConfigurable;
use Poirot\Std\Interfaces\Struct\iDataOptions;

interface iEntityPostContentObject
    extends iDataOptions
    , ipConfigurable
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
