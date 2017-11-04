<?php
namespace Module\Content\Model\Entity;

use Module\Content\Interfaces\Model\Entity\iEntityComment;
use Poirot\Std\Struct\DataOptionsOpen;


class EntityComment
    extends DataOptionsOpen
    implements iEntityComment
{
    const MODEL_POSTS = 'posts';


    protected $uid;
    protected $content;
    protected $itemIdentifier;
    protected $ownerIdentifier;
    protected $model;
    protected $voteCount = 0;
    protected $stat = self::STAT_PUBLISH;
    /** @var \DateTime */
    protected $datetimeCreated;


    /**
     * Set Unique Comment Identifier
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
     * Get Comment Unique Identifier
     *
     * @return mixed
     */
    function getUid()
    {
        return $this->uid;
    }

    /**
     * Set Content
     *
     * @param string $content
     *
     * @return $this
     */
    function setContent($content)
    {
        $this->content = (string) $content;
        return $this;
    }

    /**
     * Comment Content Message
     *
     * @return string
     */
    function getContent()
    {
        return $this->content;
    }

    /**
     * Set Item Identifier
     *
     * @param mixed $identifier
     *
     * @return $this
     */
    function setItemIdentifier($identifier)
    {
        $this->itemIdentifier = $identifier;
        return $this;
    }

    /**
     * Item Which You Have Liked From Model X
     *
     * @return mixed
     */
    function getItemIdentifier()
    {
        return $this->itemIdentifier;
    }

    /**
     * Set Owner Identifier
     *
     * @param mixed $identifier
     *
     * @return $this
     */
    function setOwnerIdentifier($identifier)
    {
        $this->ownerIdentifier = $identifier;
        return $this;
    }

    /**
     * Get Owner Who Like Something
     *
     * @return mixed
     */
    function getOwnerIdentifier()
    {
        return $this->ownerIdentifier;
    }

    /**
     * Set Model Namespace
     *
     * @param string $model
     *
     * @return $this
     */
    function setModel($model)
    {
        $this->model = (string) $model;
        return $this;
    }

    /**
     * Model Namespace
     * allow to like multiple media items, i.e. wall posts and videos
     *
     * @return string
     */
    function getModel()
    {
        return $this->model;
    }

    /**
     * Set Comment Vote Up/Down Count
     *
     * @param int $count
     *
     * @return $this
     */
    function setVoteCount($count)
    {
        $this->voteCount = $count;
        return $this;
    }

    /**
     * Get Comment Vote Number With Vote Up Or Down
     *
     * @return int
     */
    function getVoteCount()
    {
        return $this->voteCount;
    }

    /**
     * Set Comment Stat
     *
     * @param string $stat
     *
     * @return $this
     */
    function setStat($stat)
    {
        $this->stat = ($stat !== null) ? (string) $stat : null;
        return $this;
    }

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
    function getStat()
    {
        return $this->stat;
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
            $this->setDateTimeCreated(new \DateTime);

        return $this->datetimeCreated;
    }
}
