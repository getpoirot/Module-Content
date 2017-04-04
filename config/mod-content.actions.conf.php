<?php
use Poirot\Ioc\Container\BuildContainer;

/**
 * Registered Actions For Content
 * @see \Poirot\Ioc\Container\BuildContainer
 */
return array(
    'services' => array(
        // Post
        'CreatePostAction'          => \Module\Content\Actions\Posts\CreatePostAction::class,
        'EditPostAction'            => \Module\Content\Actions\Posts\EditPostAction::class,
        'DeletePostAction'          => \Module\Content\Actions\Posts\DeletePostAction::class,
        'RetrievePostAction'        => \Module\Content\Actions\Posts\RetrievePostAction::class,

        'IsUserPermissionOnContent' => \Module\Content\Actions\IsUserPermissionOnContent::class,

        // Like
        'LikePostAction'                => \Module\Content\Actions\Likes\LikePostAction::class,
        'UnLikePostAction'              => \Module\Content\Actions\Likes\UnLikePostAction::class,
        'ListPostLikesAction'           => \Module\Content\Actions\Likes\ListPostLikesAction::class,
        'ListPostsWhichUserLikedAction' => \Module\Content\Actions\Likes\ListPostsWhichUserLikedAction::class,

        'ListPostsLikedByUser'          => \Module\Content\Actions\ListPostsLikedByUser::class,

        // Comment
        'AddCommentOnPostAction' => \Module\Content\Actions\Comments\AddCommentOnPostAction::class,

    ),
);
