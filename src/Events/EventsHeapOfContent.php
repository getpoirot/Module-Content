<?php
namespace Module\Content\Events;

use Module\Content\Interfaces\Model\Entity\iEntityComment;
use Module\Content\Model\Entity\EntityPost;
use Poirot\Events\Event;
use Poirot\Events\EventHeap;


class EventsHeapOfContent
    extends EventHeap
{
    const RETRIEVE_POST          = 'retrieve.content';
    const RETRIEVE_POST_RESULT   = 'retrieve.content.result';

    const LIST_POSTS_RESULTSET   = 'list.posts.result';

    const AFTER_CREATE_CONTENT   = 'post.create.content';
    const BEFORE_CREATE_CONTENT  = 'pre.create.content';

    const BEFORE_ADD_COMMENT     = 'before.comment.add';
    const AFTER_ADD_COMMENT      = 'after.comment.add';


    /**
     * Initialize
     *
     */
    function __init()
    {
        $this->collector = new DataCollector;

        // attach default event names:
        $this->bind( new Event(self::RETRIEVE_POST) );
        $this->bind( new Event(self::RETRIEVE_POST_RESULT) );
        $this->bind( new Event(self::BEFORE_CREATE_CONTENT) );
        $this->bind( new Event(self::AFTER_CREATE_CONTENT) );
        $this->bind( new Event(self::LIST_POSTS_RESULTSET) );

        // Comments:
        $this->bind( new Event(self::BEFORE_ADD_COMMENT, new Event\BuildEvent([
            'collector' => new DataTransferOfComments ])) );

        $this->bind( new Event(self::AFTER_ADD_COMMENT, new Event\BuildEvent([
            'collector' => new DataTransferOfComments ])) );
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

class DataTransferOfComments
    extends Event\DataCollector
{
    /** @var iEntityComment */
    protected $comment;
    /** @var array */
    protected $result;


    /**
     * @return iEntityComment
     */
    function getComment()
    {
        return $this->comment;
    }

    /**
     * @param iEntityComment $comment
     */
    function setComment(iEntityComment $comment = null)
    {
        $this->comment = $comment;
    }


    // after.comment.add

    /**
     * @return array
     */
    function getResult()
    {
        return $this->result;
    }

    /**
     * @param array $result
     */
    function setResult($result)
    {
        $this->result = $result;
    }
}

class DataCollector
    extends \Poirot\Events\Event\DataCollector
{
    protected $me;
    /** @var EntityPost\ */
    protected $entity;
    protected $result;
    protected $posts;


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

    function setEntityPost(EntityPost $post = null)
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

    // list.posts.result

    function setPosts($posts)
    {
        $this->posts = $posts;
    }

    function getPosts()
    {
        return $this->posts;
    }
}
