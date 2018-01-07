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

        ## Retrieve profiles for original post owners in case of Re-Posts (if any)
        #
        $originalPostOwners = [];

        foreach ($posts as &$p) {
            $ownerId = (string) $p['user']['uid'];
            $postOwners[$ownerId] = true;

            if ('repost' == $p['content']['content_type']) {
                $ownerId = (string) $p['content']['owner_identifier'];
                $originalPostOwners[$ownerId] = true;
            }
        }

        $postOwners = \array_keys($postOwners);
        $originalPostOwners = \array_keys($originalPostOwners);

        /** @var RetrieveProfiles $funListUsers */
        $profiles = \Module\Profile\Actions::RetrieveProfiles(\array_unique($postOwners+$originalPostOwners));


        foreach ($posts as &$p) {
            $uid  = (string) $p['user']['uid'];

            /** @see \Module\Content\toArrayResponseFromPostEntity() */
            if ( isset($profiles[$uid]) )
                $p['user'] = $profiles[$uid];
            else
                $p['user']['uid'] = $uid;

            ## Re-Posts
            #
            if ('repost' == $p['content']['content_type']) {
                $originalOwnerIdentifier = (string) $p['content']['owner_identifier'];

                if ( isset($profiles[$originalOwnerIdentifier]) )
                    $p['content']['user'] = $profiles[$originalOwnerIdentifier];
                else
                    $p['content']['user']['uid'] = $originalOwnerIdentifier;

                unset($p['content']['owner_identifier']);
                $p['content']['original_post_id'] = (string)$p['content']['uid'];
                unset($p['content']['uid']);
            }
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
