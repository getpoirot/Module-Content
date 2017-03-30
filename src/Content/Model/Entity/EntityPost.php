<?php
namespace Module\Content\Model\Entity;

use Module\Content\Model\Entity\EntityPost\GeoObject;


class EntityPost
    extends EntityPostBase
{
    protected $owner;
    /** @var GeoObject */
    protected $geoLocation;


    /**
     * Set Owner (Created By)
     *
     * @param mixed $ownerIdentifier
     *
     * @return $this
     */
    function setOwnerIdentifier($ownerIdentifier)
    {
        $this->owner = $ownerIdentifier;
        return $this;
    }

    /**
     * Get Owner Identifier
     *
     * @return mixed
     */
    function getOwnerIdentifier()
    {
        return $this->owner;
    }

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
