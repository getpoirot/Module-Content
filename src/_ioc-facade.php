<?php
namespace Module\Content\Actions
{
    use Module\Content\Actions\Comments\AddCommentOnPostAction;
    use Module\Content\Actions\Comments\ListCommentsOfPostAction;
    use Module\Content\Actions\Comments\RemoveCommentFromPostAction;
    use Module\Content\Actions\Likes\LikePostAction;
    use Module\Content\Actions\Likes\ListPostLikesAction;
    use Module\Content\Actions\Likes\ListPostsWhichUserLikedAction;
    use Module\Content\Actions\Likes\UnLikePostAction;
    use Module\Content\Actions\Posts\BrowsePostsAction;
    use Module\Content\Actions\Posts\CreatePostAction;
    use Module\Content\Actions\Posts\DeletePostAction;
    use Module\Content\Actions\Posts\EditPostAction;
    use Module\Content\Actions\Posts\ListPostsOfMeAction;
    use Module\Content\Actions\Posts\ListPostsOfUserAction;
    use Module\Content\Actions\Posts\RetrievePostAction;


    /**
     * @property CreatePostAction      $CreatePostAction
     * @property EditPostAction        $EditPostAction
     * @property DeletePostAction      $DeletePostAction
     * @property RetrievePostAction    $RetrievePostAction
     * @property ListPostsOfMeAction   $ListPostsOfMeAction
     * @property ListPostsOfUserAction $ListPostsOfUserAction
     * @property BrowsePostsAction     $BrowsePostsAction
     *
     * @property LikePostAction                $LikePostAction
     * @property UnLikePostAction              $UnLikePostAction
     * @property ListPostLikesAction           $ListPostLikesAction
     * @property ListPostsWhichUserLikedAction $ListPostsWhichUserLikedAction
     *
     * @property AddCommentOnPostAction      $AddCommentOnPostAction
     * @property RemoveCommentFromPostAction $RemoveCommentFromPostAction
     * @property ListCommentsOfPostAction    $ListCommentsOfPostAction
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
