<?php
namespace Module\Content\Events;

use Module\Content\Model\Entity\EntityPost;
use Poirot\Events\Event;
use Poirot\Events\EventHeap;


class EventsHeapOfContent
    extends EventHeap
{
    const RETRIEVE_CONTENT        = 'retrieve.content';
    const RETRIEVE_CONTENT_RESULT = 'retrieve.content.result';
    const AFTER_CREATE_CONTENT    = 'post.create.content';
    const BEFORE_CREATE_CONTENT   = 'pre.create.content';
    const RETRIEVE_POSTS_RESULT   = 'retrieve.posts.result';


    /**
     * Initialize
     *
     */
    function __init()
    {
        $this->collector = new DataCollector;

        // attach default event names:
        $this->bind( new Event(self::RETRIEVE_CONTENT) );
        $this->bind( new Event(self::RETRIEVE_CONTENT_RESULT) );
        $this->bind( new Event(self::BEFORE_CREATE_CONTENT) );
        $this->bind( new Event(self::AFTER_CREATE_CONTENT) );
        $this->bind( new Event(self::RETRIEVE_POSTS_RESULT) );
    }


    /**
     * @override ide auto info
     * @inheritdoc
     *
     * @return DataCollector
     */
    function collector($options = null)
    {
        return parent::collector($options);
    }
}

class DataCollector
    extends \Poirot\Events\Event\DataCollector
{
    protected $me;
    /** @var EntityPost\ */
    protected $entity;
    protected $result;


    /**
     * Who Request The Page (user session)
     * @param mixed|null $me User identifier
     */
    function setMe($me)
    {
        $this->me = $me;
    }

    function getMe()
    {
        return $this->me;
    }

    function setEntityPost(EntityPost $post)
    {
        $this->entity = $post;
    }

    function getEntityPost()
    {
        return $this->entity;
    }


    // .. retrieve.content.result

    function getResult()
    {
        return $this->result;
    }

    function setResult($result)
    {
        $this->result = $result;
    }
}
