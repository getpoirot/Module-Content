<?php
namespace Module\Content\Interfaces\Model\Entity;


interface iEntityPost
{
    /**
     * Get Content Unique Identifier
     *
     * @return mixed
     */
    function getUid();

    /**
     * Get Key/Value Content
     *
     * @return iEntityPostContentObject
     */
    function getContent();

    /**
     * Get Post Stat
     * values: publish|draft|locked
     *
     * @return string
     */
    function getStat();

    /**
     * Get Share Stat
     * values: public|private
     *
     * @return string
     */
    function getStatShare();

    /**
     * Get Time Stamp Created
     *
     * @return int Timestamp Created
     */
    function getTimestampCreated();
}
