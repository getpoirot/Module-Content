<?php
namespace Module\Content\Model\Entity;

use Module\Content\Interfaces\Model\Entity\iEntityPost;
use Module\Content\Interfaces\Model\Entity\iEntityPostContentObject;
use Poirot\Std\Struct\DataOptionsOpen;


class EntityPostBase
    extends DataOptionsOpen
    implements iEntityPost
{
    const STAT_PUBLISH = 'publish';
    const STAT_DRAFT   = 'draft';
    const STAT_LOCKED  = 'locked';

    const STAT_SHARE_PUBLIC  = 'public';
    const STAT_SHARE_PRIVATE = 'private';


    protected $uid;
    protected $content;
    protected $stat;
    protected $statShare;
    protected $datetimeCreated;

    protected $_available_stat = [
        self::STAT_PUBLISH,
        self::STAT_DRAFT,
        self::STAT_LOCKED,
    ];

    protected $_available_stat_share = [
        self::STAT_SHARE_PUBLIC,
        self::STAT_SHARE_PRIVATE,
    ];


    /**
     * Set Unique Content Identifier
     *
     * @param mixed $uid
     *
     * @return $this
     */
    function setUid($uid)
    {
        $this->uid = $uid;
        return $this;
    }

    /**
     * Get Content Unique Identifier
     *
     * @return mixed
     */
    function getUid()
    {
        return $this->uid;
    }

    /**
     * Set Post Content
     *
     * @param iEntityPostContentObject $content
     *
     * @return $this
     */
    function setContent(iEntityPostContentObject $content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Get Key/Value Content
     *
     * @return iEntityPostContentObject
     */
    function getContent()
    {
        return $this->content;
    }

    /**
     * Set Publish Stat
     *
     * @param string $stat
     *
     * @return $this
     */
    function setStat($stat)
    {
        $stat = (string) $stat;
        if (!in_array($stat, $this->_available_stat))
            throw new \InvalidArgumentException(sprintf('Stat (%s) is Unknown.', $stat));

        $this->stat = $stat;
        return $this;
    }

    /**
     * Get Post Stat
     * values: publish|draft|locked
     *
     * @return string
     */
    function getStat()
    {
        return $this->stat;
    }

    /**
     * Set Stat Share
     *
     * @param string $stat
     *
     * @return $this
     */
    function setStatShare($stat)
    {
        $stat = (string) $stat;
        if (!in_array($stat, $this->_available_stat_share))
            throw new \InvalidArgumentException(sprintf('Stat (%s) is Unknown.', $stat));

        $this->statShare = $stat;
        return $this;
    }

    /**
     * Get Share Stat
     * values: public|private
     *
     * @return string
     */
    function getStatShare()
    {
        return $this->statShare;
    }

    /**
     * Set Created Timestamp
     *
     * @param \DateTime|null $dateTime
     *
     * @return $this
     */
    function setDateTimeCreated($dateTime)
    {
        if ( !($dateTime === null || $dateTime instanceof \DateTime) )
            throw new \InvalidArgumentException(sprintf(
                'Datetime must instance of \Datetime or null; given: (%s).'
                , \Poirot\Std\flatten($dateTime)
            ));

        $this->datetimeCreated = $dateTime;
        return $this;
    }

    /**
     * Get Date Time Created
     *
     * @return \DateTime
     */
    function getDateTimeCreated()
    {
        if (!$this->datetimeCreated)
            $this->setDateTimeCreated(new \DateTime());

        return $this->datetimeCreated;
    }
}
