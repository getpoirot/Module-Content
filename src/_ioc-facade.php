<?php
namespace Module\Content\Actions
{
    use Module\Content\Actions\Posts\CreatePost;
    use Module\Content\Actions\Posts\CreatePostAction;

    /**
     * @property CreatePost       $CreatePost
     * @property CreatePostAction $CreatePostAction
     *
     * @method static mixed CreatePost()
     */
    class IOC extends \IOC
    { }
}

namespace Module\Content\Services
{
    /**
     * @method static ContainerCappedContentObject ContentObjectContainer()
     */
    class IOC extends \IOC
    { }
}
