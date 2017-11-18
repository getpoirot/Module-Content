<?php
/**
 * Registered Actions For Content
 * @see \Poirot\Ioc\Container\BuildContainer
 */
return [
    'services' => [
        // Post
        'ListPostsOfUser'           => \Module\Content\Actions\ListPostsOfUser::class,
        'FindLatestPosts'           => \Module\Content\Actions\Posts\FindLatestPosts::class,
        'IsUserPermissionOnContent' => \Module\Content\Actions\IsUserPermissionOnContent::class,


        // Like
        'ListPostsLikedByUser'          => \Module\Content\Actions\ListPostsLikedByUser::class,
    ],
];
