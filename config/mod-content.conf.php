<?php
use Module\HttpFoundation\Actions\Url;

return [

    \Module\Foundation\Services\PathService::CONF => [
        'paths' => [
            // According to route name 'www-assets' to serve statics files
            // @see cor-http_foundation.routes
            'mod-content-media_cdn' => function($args) {
                $uri = $this->assemble('$serverUrlTenderBin', $args);
                return $uri;
            },
        ],
        'variables' => [
            'serverUrlTenderBin' => function() {
                return \Module\HttpFoundation\Actions::url(
                    'main/tenderbin/resource/get'
                    , [ 'resource_hash' => '$hash' ]
                    , Url::INSTRUCT_NOTHING | Url::ABSOLUTE_URL
                );
            },
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
