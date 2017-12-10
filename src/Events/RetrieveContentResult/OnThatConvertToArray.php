<?php
namespace Module\Content\Events;

use Poirot\Std\Type\StdTravers;


class OnThatConvertToArray
{
    /**
     * Ensure that Mongo Cursor has lived
     *
     * @param \Traversable $posts
     * @param mixed        $me
     *
     * @return array
     */
    function __invoke($posts, $me)
    {
        if ( is_array($posts) )
            return;


        $posts = StdTravers::of($posts)->toArray();

        return [
            'posts' => $posts,
        ];
    }
}
