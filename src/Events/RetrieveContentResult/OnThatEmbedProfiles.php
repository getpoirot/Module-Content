<?php
namespace Module\Content\Events\RetrieveContentResult;

use Module\Content\Interfaces\Model\Entity\iEntityPostContentObject;
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
        foreach ($posts as $p) {
            $ownerId = (string) $p['user']['uid'];
            if ( empty($ownerId) )
                continue;


            $postOwners[$ownerId] = true;

            $content = $p['content'];
            if (is_array($content)) {
                // TODO Deprecated
                if ('repost' == $content['content_type']) {
                    $ownerId = (string) $p['content']['owner_identifier'];
                    $postOwners[$ownerId] = true;
                }
            } elseif ($content instanceof iEntityPostContentObject) {
                if ('repost' == $content->getContentType()) {
                    $ownerId = (string) $content->getOWnerIdentifier();
                    $postOwners[$ownerId] = true;
                }
            }
        }

        /** @var RetrieveProfiles $funListUsers */
        $profiles = \Module\Profile\Actions::RetrieveProfiles(array_keys($postOwners));


        foreach ($posts as &$p) {
            $uid  = (string) $p['user']['uid'];
            if ( empty($uid) )
                continue;


            /** @see \Module\Content\toArrayResponseFromPostEntity() */
            if ( isset($profiles[$uid]) )
                $p['user'] = $profiles[$uid];
            else
                $p['user']['uid'] = $uid;

            ## Re-Posts
            #
            $content = $p['content'];
            if (is_array($content)) {
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
            } elseif ($content instanceof iEntityPostContentObject) {
                if ('repost' == $content->getContentType()) {
                    $originalOwnerIdentifier = (string) $content->getOwnerIdentifier();

                    if ( isset($profiles[$originalOwnerIdentifier]) )
                        $content['user'] = $profiles[$originalOwnerIdentifier];
                    else
                        $p['content']['user']['uid'] = $originalOwnerIdentifier;

                    unset($p['content']['owner_identifier']);
                    $p['content']['original_post_id'] = (string)$p['content']['uid'];
                    unset($p['content']['uid']);
                }
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
