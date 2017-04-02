<?php
use Poirot\Ioc\Container\BuildContainer;

/**
 * Registered Actions For Content
 * @see \Poirot\Ioc\Container\BuildContainer
 */
return array(
    'services' => array(
        'CreatePostAction'          => \Module\Content\Actions\Posts\CreatePostAction::class,
        'EditPostAction'            => \Module\Content\Actions\Posts\EditPostAction::class,
        'DeletePostAction'          => \Module\Content\Actions\Posts\DeletePostAction::class,
        'RetrievePostAction'        => \Module\Content\Actions\Posts\RetrievePostAction::class,

        'LikePostAction'            => \Module\Content\Actions\Likes\LikePostAction::class,
        'UnLikePostAction'          => \Module\Content\Actions\Likes\UnLikePostAction::class,
        'ListPostLikesAction'       => \Module\Content\Actions\Likes\ListPostLikesAction::class,
        'ListWhoLikesPost'          => \Module\Content\Actions\ListWhoLikesPost::class,

        'IsUserPermissionOnContent' => \Module\Content\Actions\IsUserPermissionOnContent::class,
    ),
);
