<?php
namespace Module\Content\Events\RetrieveContentResult;

use Poirot\Events\Event;


class OnThatPersistFromCursor
{
    /**
     * Ensure that Mongo Cursor has lived
     *
     * @param \Traversable $posts
     * @param mixed $me
     * @param Event $e
     *
     * @return array
     */
    function __invoke($posts, $me, $e)
    {
        if (! is_array($posts) )
            $posts = iterator_to_array($posts);


        if ( empty($posts) )
            // Posts are empty we have no work!!
            $e->stopPropagation();

        return [
            'posts' => $posts,
        ];
    }
}
