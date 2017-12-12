<?php
namespace Module\Content\Events\RetrieveContent;

use Module\Content\Model\Entity\EntityPost;


class OnRetrieveContentToDataResponse
{
    /**
     * Embed Profiles Data Into Posts Result
     *
     * @param EntityPost $entity_post
     * @param mixed      $me
     *
     * @return array
     */
    function __invoke($entity_post, $me)
    {
        $r = \Module\Content\toArrayResponseFromPostEntity($entity_post, $me) + [
            '_self' => [
                'content_id' => (string) $entity_post->getUid(),
            ],
        ];

        return [
            'result' => $r,
        ];
    }
}
