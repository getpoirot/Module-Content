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

use Poirot\Ioc\instance;

return [
    'services' => [
        'ClientTender' => new instance(
            \Module\Content\Services\ServiceClientTender::class
            , \Poirot\Std\catchIt(function () {
                if (false === $c = \Poirot\Config\load(__DIR__.'/content/client-tenderbin'))
                    throw new \Exception('Config (content/client-tenderbin) not loaded.');

                return $c->value;
            })
        ),

        'ContentObjectContainer' => \Module\Content\Services\ContainerCappedContentObject::class,
    ],
    'nested' => [
        'repository' => [
            // Define Default Services
            'services' => [
                'Posts'    => \Module\Content\Model\Driver\Mongo\PostsRepoService::class,
                'Likes'    => \Module\Content\Model\Driver\Mongo\LikesRepoService::class,
                'Comments' => \Module\Content\Model\Driver\Mongo\CommentsRepoService::class,
            ],
        ],
    ],
];
