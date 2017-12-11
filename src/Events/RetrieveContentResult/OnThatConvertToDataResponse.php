<?php
namespace Module\Content\Events\RetrieveContentResult;

use Module\Content\Model\Entity\EntityPost;
use Poirot\Std\Type\StdTravers;


class OnThatConvertToDataResponse
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
        /** @var EntityPost $post */
        $posts = StdTravers::of($posts)->chain(function ($post, $_, $cr = []) use ($me) {
            $cr[] = \Module\Content\toArrayResponseFromPostEntity($post, $me);
            return $cr;
        }, [] /* when list is empty */);


        return [
            'posts' => $posts,
        ];
    }
}
