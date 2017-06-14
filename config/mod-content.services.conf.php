<?php
/**
 * Default Content IOC Services
 *
 * @see \Poirot\Ioc\Container\BuildContainer
 *
 * ! These Services Can Be Override By Name (also from other modules).
 *   Nested in IOC here at: /module/tenderbin/services
 *
 *
 * @see \Module\Content::getServices()
 */

return [
    'services' => [
        'ClientTender' => \Module\Content\Services\ServiceClientTender::class,
        'ContentObjectContainer' => \Module\Content\Services\ContainerCappedContentObject::class,
    ],
    'nested' => [
        'repository' => [
            // Define Default Services
            'services' => [
                \Module\Content\Model\Driver\Mongo\PostsRepoService::class,
                \Module\Content\Model\Driver\Mongo\LikesRepoService::class,
                \Module\Content\Model\Driver\Mongo\CommentsRepoService::class,
            ],
        ],
    ],
];
