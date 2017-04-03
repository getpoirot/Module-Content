<?php
namespace Module\Content\Actions
{

    use Module\Content\Actions\Likes\LikePostAction;
    use Module\Content\Actions\Likes\ListPostLikesAction;
    use Module\Content\Actions\Likes\ListPostsWhichUserLikedAction;
    use Module\Content\Actions\Likes\UnLikePostAction;
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
     * @property LikePostAction                $LikePostAction
     * @property UnLikePostAction              $UnLikePostAction
     * @property ListPostLikesAction           $ListPostLikesAction
     * @property ListPostsWhichUserLikedAction $ListPostsWhichUserLikedAction
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
