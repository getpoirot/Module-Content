<?php
namespace Module\Content\Model\Entity\EntityPost;

use Poirot\Std\Struct\aDataOptions;


class GeoObject
    extends aDataOptions
{
    /** @var array [lon, lat] */
    protected $geo;
    /** @var string Geo Lookup Caption  */
    protected $caption;


    /**
     * Set GeoLocation
     *
     * the first field should contain the longitude value and the second
     * field should contain the latitude value.
     *
     * [ "lon": 28.5122077,
     *   "lat": 53.5818702 ]
     * or
     * [ 28.5122077, 53.5818702 ]
     *
     * @param array [lon, lat] $location
     *
     * @return $this
     */
    function setGeo($location)
    {
        if (isset($location['lon']))
            $this->geo = array($location['lon'], $location['lat']);
        else
            $this->geo = array($location[0], $location[1]);

        return $this;
    }

    /**
     * Get Geo Location
     *
     * @return array
     */
    function getGeo()
    {
        return $this->geo;
    }

    /**
     * Set Geo Lookup Caption
     *
     * @param string $entitle
     *
     * @return $this
     */
    function setCaption($entitle)
    {
        $this->caption = (string) $entitle;
        return $this;
    }

    /**
     * Get Geo Lookup Caption
     *
     * @return string
     */
    function getCaption()
    {
        return $this->caption;
    }
}
