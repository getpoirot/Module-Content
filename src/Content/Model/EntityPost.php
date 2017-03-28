<?php
namespace Module\Content\Model;


class EntityPost
    extends BaseEntityPost
{
    /** @var EntityPostGeoObject */
    protected $geoLocation;


    /**
     * Set GeoLocation
     *
     * @param EntityPostGeoObject|null $location
     *
     * @return $this
     */
    function setLocation($location)
    {
        if ($location !== null && !$location instanceof EntityPostGeoObject)
            throw new \InvalidArgumentException;

        $this->geoLocation = $location;
        return $this;
    }

    /**
     * Get Geo Location
     *
     * @return EntityPostGeoObject|null
     */
    function getLocation()
    {
        return $this->geoLocation;
    }
}
