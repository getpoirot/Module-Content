<?php
namespace Module\Content\Model\Entity;

use Module\Content\Model\Entity\EntityPost\GeoObject;


class EntityPost
    extends EntityPostBase
{
    /** @var GeoObject */
    protected $geoLocation;


    /**
     * Set GeoLocation
     *
     * @param GeoObject|null $location
     *
     * @return $this
     */
    function setLocation($location)
    {
        if ($location !== null && !$location instanceof GeoObject)
            throw new \InvalidArgumentException;

        $this->geoLocation = $location;
        return $this;
    }

    /**
     * Get Geo Location
     *
     * @return GeoObject|null
     */
    function getLocation()
    {
        return $this->geoLocation;
    }
}
