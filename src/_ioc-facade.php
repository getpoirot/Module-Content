<?php
namespace Module\Content\Actions
{
    use Module\Content\Actions\Posts\CreatePost;
    use Module\Content\Actions\Posts\CreatePostAction;
    use Module\Content\Model\Entity\EntityPost;

    /**
     * @property CreatePost       $CreatePost
     * @property CreatePostAction $CreatePostAction
     *
     * @method static EntityPost CreatePost(EntityPost $post)
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
