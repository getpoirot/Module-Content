<?php
use Poirot\Ioc\Container\BuildContainer;

/**
 * Registered Actions For Content
 * @see \Poirot\Ioc\Container\BuildContainer
 */
return array(
    'services' => array(
        'createPost'       => \Module\Content\Actions\Posts\CreatePost::class,
        'createPostAction' => \Module\Content\Actions\Posts\CreatePostAction::class,
    ),
);
