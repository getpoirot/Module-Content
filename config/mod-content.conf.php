<?php
return [

    ## ----------------------------------- ##
    ## OAuth2Client Module Must Configured ##
    ## to assert tokens ...                ##
    ## ----------------------------------- ##

    \Module\OAuth2Client\Module::CONF_KEY => [
        // Configure module ....
    ],


    # Mongo Driver:

    Module\MongoDriver\Module::CONF_KEY =>
    [
        \Module\MongoDriver\Services\aServiceRepository::CONF_KEY =>
        [
            \Module\Content\Model\Mongo\PostsRepoService::class => [
                'collection' => [
                    // query on which collection
                    'name' => 'posts',
                    // which client to connect and query with
                    'client' => \Module\MongoDriver\Module\MongoDriverManagementFacade::CLIENT_DEFAULT,
                    // ensure indexes
                    'indexes' => [
                        ['key' => ['_id' => 1]],
                    ],],],
        ],
    ],
];
