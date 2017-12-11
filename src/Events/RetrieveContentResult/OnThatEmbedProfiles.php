<?php
namespace Module\Content\Events\RetrieveContentResult;

use Module\Profile\Actions\Helpers\RetrieveProfiles;


class OnThatEmbedProfiles
{
    /**
     * Embed Profiles Data Into Posts Result
     *
     * @param \Traversable $posts
     * @param mixed        $me
     *
     * @return array
     */
    function __invoke($posts = null, $me = null, $result = null)
    {
        if ($result !== null)
            // Attached To retrieve_post event
            /** @see EventsHeapOfContent */
            $posts = [$result];


        if ( empty($posts) )
            return;


        ## Retrieve Profiles For Posts Owner
        #
        $postOwners = [];
        foreach ($posts as &$p) {
            $ownerId = (string) $p['user']['uid'];
            $postOwners[$ownerId] = true;
        }

        $postOwners = \array_keys($postOwners);

        /** @var RetrieveProfiles $funListUsers */
        $profiles = \Module\Profile\Actions::RetrieveProfiles($postOwners);


        foreach ($posts as &$p) {
            $uid  = (string) $p['user']['uid'];

            /** @see \Module\Content\toArrayResponseFromPostEntity() */
            if ( isset($profiles[$uid]) )
                $p['user'] = $profiles[$uid];
            else
                $p['user']['uid'] = (string) $p['user']['uid'];
        }


        if ($result !== null)
            // Attached To retrieve_post event
            /** @see EventsHeapOfContent */
            return [
                'result' => current($posts),
            ];

        return [
            'posts' => $posts,
        ];
    }
}
