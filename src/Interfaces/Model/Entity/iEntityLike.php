<?php
namespace Module\Content\Interfaces\Model\Entity;


interface iEntityLike
{
    /**
     * Unique Identifier
     *
     * note: to ease search we can create identifier
     *       from given owner_identifier, item_identifier, model
     *
     * @return mixed
     */
    function getIdentifier();

    /**
     * Get Owner Who Like Something
     *
     * @return mixed
     */
    function getOwnerIdentifier();

    /**
     * Item Which You Have Liked From Model X
     *
     * @return mixed
     */
    function getItemIdentifier();

    /**
     * Model Namespace
     * allow to like multiple media items, i.e. wall posts and videos
     *
     * @return string
     */
    function getModel();

    /**
     * Get DateTime Created
     *
     * @return \DateTime
     */
    function getDatetimeCreated();
}
