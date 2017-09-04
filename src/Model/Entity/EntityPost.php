<?php
namespace Module\Content\Model\Entity;

use Module\Content\Model\Entity\EntityPost\GeoObject;
use Module\Content\Model\Entity\EntityPost\LikesObject;


class EntityPost
    extends EntityPostBase
{
    protected $owner;
    /** @var GeoObject */
    protected $geoLocation;
    /** @var LikesObject */
    protected $likes;
    protected $isCommentEnabled = true;


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
     * @param GeoObject|null $objLocation
     *
     * @return $this
     */
    function setLocation($objLocation)
    {
        if ($objLocation !== null && !$objLocation instanceof GeoObject)
            throw new \InvalidArgumentException(sprintf(
                'Location Must instanceof GeoObject or null; given: (%s).'
                , \Poirot\Std\flatten($objLocation)
            ));


        $this->geoLocation = $objLocation;
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

    /**
     * Set Embed Likes
     *
     * @param LikesObject|null $objLike
     *
     * @return $this
     */
    function setLikes($objLike)
    {
        if ($objLike !== null && !$objLike instanceof LikesObject)
            throw new \InvalidArgumentException(sprintf(
                'Location Must instanceof LikeObject or null; given: (%s).'
                , \Poirot\Std\flatten($objLike)
            ));


        $this->likes = $objLike;
        return $this;
    }

    /**
     * Get Likes
     *
     * @return LikesObject
     */
    function getLikes()
    {
        return $this->likes;
    }

    /**
     * Set Whether Comments Are Allowed On Post
     *
     * @param bool $enabled
     *
     * @return $this
     */
    function setIsCommentEnabled($enabled = true)
    {
        $this->isCommentEnabled = (bool) $enabled;
        return $this;
    }

    /**
     * Whether Comments Are Allowed On Post?
     *
     * @return bool
     */
    function getIsCommentEnabled()
    {
        return $this->isCommentEnabled;
    }
}
