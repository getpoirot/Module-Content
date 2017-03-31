<?php
namespace Module\Content\Actions
{
    use Module\Content\Actions\Posts\CreatePostAction;
    use Module\Content\Actions\Posts\DeletePostAction;
    use Module\Content\Actions\Posts\EditPostAction;
    use Module\Content\Actions\Posts\RetrievePostAction;

    /**
     * @property CreatePostAction   $CreatePostAction
     * @property EditPostAction     $EditPostAction
     * @property DeletePostAction   $DeletePostAction
     * @property RetrievePostAction $RetrievePostAction
     *
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
