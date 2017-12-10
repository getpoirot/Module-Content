<?php
namespace Module\Content\Events;

use Module\Content\Model\Entity\EntityPost;
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
    function __invoke($posts, $me)
    {
        $postsArray = [];

        ## Retrieve Profiles For Posts Owner
        #
        $postOwners = [];
        /** @var EntityPost $p */
        foreach ($posts as $p) {
            \array_push($postsArray, $p);
            $ownerId = (string) $p->getOwnerIdentifier();
            $postOwners[$ownerId] = true;
        }

        $postOwners = \array_keys($postOwners);

        /** @var RetrieveProfiles $funListUsers */
        $profiles = \Module\Profile\Actions::RetrieveProfiles($postOwners);


        foreach ($postsArray as $p) {
            $uid  = (string) $p->getOwnerIdentifier();

            if ( isset($profiles[$uid]) )
                // set with open options
                $p->setOwnerProfile($profiles[$uid]);
        }


        return [
            'posts' => $postsArray,
        ];
    }
}
