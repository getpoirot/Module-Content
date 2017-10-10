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
        'ListPostsOfMeAction'       => \Module\Content\Actions\Posts\ListPostsOfMeAction::class,
        'ListPostsOfUserAction'     => \Module\Content\Actions\Posts\ListPostsOfUserAction::class,
        'BrowsePostsAction'         => \Module\Content\Actions\Posts\BrowsePostsAction::class,

        'IsUserPermissionOnContent' => \Module\Content\Actions\IsUserPermissionOnContent::class,
        'ListPostsOfUser'           => \Module\Content\Actions\ListPostsOfUser::class,


        // Like
        'LikePostAction'                => \Module\Content\Actions\Likes\LikePostAction::class,
        'UnLikePostAction'              => \Module\Content\Actions\Likes\UnLikePostAction::class,
        'ListPostLikesAction'           => \Module\Content\Actions\Likes\ListPostLikesAction::class,
        'ListPostsWhichUserLikedAction' => \Module\Content\Actions\Likes\ListPostsWhichUserLikedAction::class,

        'ListPostsLikedByUser'          => \Module\Content\Actions\ListPostsLikedByUser::class,


        // Comment
        'AddCommentOnPostAction'      => \Module\Content\Actions\Comments\AddCommentOnPostAction::class,
        'RemoveCommentFromPostAction' => \Module\Content\Actions\Comments\RemoveCommentFromPostAction::class,
        'ListCommentsOfPostAction'    => \Module\Content\Actions\Comments\ListCommentsOfPostAction::class,

    ),
);
