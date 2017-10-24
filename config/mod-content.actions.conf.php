<?php
/**
 * Registered Actions For Content
 * @see \Poirot\Ioc\Container\BuildContainer
 */
return array(
    'services' => array(
        // Post
        'IsUserPermissionOnContent' => \Module\Content\Actions\IsUserPermissionOnContent::class,
        'ListPostsOfUser'           => \Module\Content\Actions\ListPostsOfUser::class,


        // Like
        'ListPostsLikedByUser'          => \Module\Content\Actions\ListPostsLikedByUser::class,
    ),
);
