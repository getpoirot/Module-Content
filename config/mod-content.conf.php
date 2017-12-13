<?php
use Module\Content\Events\EventsHeapOfContent;
use Module\Content\Events\RetrieveContent\OnRetrieveContentToDataResponse;
use Module\Content\Events\RetrieveContentResult\OnThatConvertToDataResponse;
use Module\Content\Events\RetrieveContentResult\OnThatEmbedMediaLinks;
use Module\Content\Events\RetrieveContentResult\OnThatEmbedProfiles;
use Module\Content\Events\RetrieveContentResult\OnThatPersistFromCursor;

return [

    \Module\Content\Module::CONF => [

        ## Events
        #
        \Module\Content\Actions\aAction::CONF => [
            // Events Section Of Events Builder
            /** @see \Poirot\Events\Event\BuildEvent */

            EventsHeapOfContent::RETRIEVE_POST_RESULT => [
                'listeners' => [
                    ['priority' => OnThatEmbedMediaLinks::EVENT_PRIORITY,  'listener' => OnThatEmbedMediaLinks::class ],
                    ['priority' => 1100,  'listener' => OnRetrieveContentToDataResponse::class ],
                    ['priority' => 1000,  'listener' => OnThatEmbedProfiles::class ],
                ],
            ],

            EventsHeapOfContent::LIST_POSTS_RESULTSET => [
                'listeners' => [
                    ['priority' => 10000, 'listener' => OnThatPersistFromCursor::class ],
                    ['priority' => OnThatEmbedMediaLinks::EVENT_PRIORITY,  'listener' => OnThatEmbedMediaLinks::class ],
                    ['priority' => 1100,  'listener' => OnThatConvertToDataResponse::class ],
                    ['priority' => 1000,  'listener' => OnThatEmbedProfiles::class ],
                ],
            ],
        ],
    ],

    # Mongo Driver:

    Module\MongoDriver\Module::CONF_KEY =>
    [
        \Module\MongoDriver\Services\aServiceRepository::CONF_REPOSITORIES =>
        [
            \Module\Content\Model\Driver\Mongo\PostsRepoService::class => [
                'collection' => [
                    // query on which collection
                    'name' => 'content.posts',
                    // which client to connect and query with
                    'client' => 'master',
                    // ensure indexes
                    'indexes' => [
                        ['key' => ['_id' => 1]],
                        ['key' => ['owner_identifier' => 1]],
                        ['key' => ['_id' => -1, 'stat' => 1]],
                        ['key' => ['_id' => -1, 'stat' => 1, 'stat_share' => 1]],
                        ['key' => ['_id' => -1, 'owner_identifier' => 1, 'stat' => 1, 'stat_share' => 1]],
                    ],],],

            \Module\Content\Model\Driver\Mongo\LikesRepoService::class => [
                'collection' => [
                    // query on which collection
                    'name' => 'content.likes',
                    // which client to connect and query with
                    'client' => 'master',
                    // ensure indexes
                    'indexes' => [
                        ['key' => ['_id'  => 1]],
                        ['key' => ['_uid' => 1]],
                        ['key' => ['item_identifier' => 1, 'model' => 1]],
                        ['key' => ['owner_identifier' => 1, 'model' => 1]],
                    ],],],

            \Module\Content\Model\Driver\Mongo\CommentsRepoService::class => [
                'collection' => [
                    // query on which collection
                    'name' => 'content.comments',
                    // which client to connect and query with
                    'client' => 'master',
                    // ensure indexes
                    'indexes' => [
                        ['key' => ['_id'  => 1]],
                        ['key' => ['item_identifier' => 1, 'model' => 1]],
                        ['key' => ['owner_identifier' => 1, 'model' => 1]],
                    ],],],
        ],
    ],
];
