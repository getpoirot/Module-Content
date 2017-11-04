<?php
namespace Module\Content\Interfaces\Model\Entity;


interface iEntityComment
{
    const STAT_PUBLISH = 'publish';
    const STAT_IGNORE  = 'ignore';


    /**
     * Comment Identifier
     *
     * @return mixed
     */
    function getUid();

    /**
     * Comment Content Message
     *
     * @return string
     */
    function getContent();

    /**
     * Get Comment Owner
     *
     * @return mixed
     */
    function getOwnerIdentifier();

    /**
     * Item Which You Have Add a Comment From Model X
     *
     * @return mixed
     */
    function getItemIdentifier();

    /**
     * Model Namespace
     * allow to comment multiple media items, i.e. wall posts or videos
     *
     * @return string
     */
    function getModel();

    /**
     * Get Comment Vote Number With Vote Up Or Down
     *
     * @return int
     */
    function getVoteCount();

    /**
     * Get Comment Publish Stat
     *
     * comment entry has stat field; some comments may reported or ignored by
     * content owner that received comment. so stat field may changed to "ignore".
     *
     * ignored comment will not displayed
     *
     * @return string|null
     */
    function getStat();

    /**
     * Get DateTime Created
     *
     * @return \DateTime
     */
    function getDateTimeCreated();
}
