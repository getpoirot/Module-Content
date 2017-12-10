<?php
namespace Module\Content\Events\RetrieveContent;

use Module\Content\Model\Entity\EntityPost;
use Module\Profile\Actions\Helpers\RetrieveProfiles;


class OnRetrieveContentEmbedProfile
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
        $uid  = (string) $entity_post->getOwnerIdentifier();

        /** @var RetrieveProfiles $funListUsers */
        $profiles = \Module\Profile\Actions::RetrieveProfiles([$uid]);

        if ( isset($profiles[$uid]) )
            // set with open options
            $entity_post->setOwnerProfile($profiles[$uid]);


        return [
            'entity_post' => $entity_post,
        ];
    }
}
