<?php
namespace Module\Content\Actions\Posts;

use Module\Content\Actions\aAction;
use Module\Content\Interfaces\Model\Repo\iRepoPosts;
use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Poirot\Http\Interfaces\iHeader;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;
use Poirot\Std\Exceptions\exUnexpectedValue;
use Poirot\Http\HttpResponse;
use Poirot\Http\Header\FactoryHttpHeader;


/**
 * @route /browse/feeds
 */
class NewPostsCountAction
    extends aAction
{
    const X_HEADER = 'X-PostId';

    /** @var iRepoPosts */
    protected $repoPosts;

    protected $tokenMustHaveOwner  = true;

    /**
     * Construct
     *
     * @param iHttpRequest $httpRequest @IoC /HttpRequest
     * @param iRepoPosts   $repoPosts   @IoC /module/content/services/repository/Posts
     */
    function __construct(
        iHttpRequest $httpRequest
        , iRepoPosts $repoPosts
    ) {
        $this->repoPosts = $repoPosts;

        parent::__construct($httpRequest);
    }


    /**
     * @param iAccessToken|null $token
     *
     * @return array
     * @throws \Exception
     */
    function __invoke($token = null)
    {
        ## Assert Token
        #
        $this->assertTokenByOwnerAndScope($token);

        if ( ! $this->request->headers()->has(self::X_HEADER))
            throw exUnexpectedValue::paramIsRequired('post_id');

        /** @var iHeader $postIdHeader */
        $postIdHeader = $this->request->headers()->get(self::X_HEADER)->current();
        $postId       = $postIdHeader->renderValueLine();

        $me     = $token->getOwnerIdentifier();
        $count  = $this->repoPosts->countNewPosts($me, $postId);

        return $this->_respond($count);
    }

    private function _respond($count)
    {
        # Build Response
        #
        $response = new HttpResponse;
        $response->headers()
            ->insert(FactoryHttpHeader::of(['Cache-Control' => 'no-cache, no-store, must-revalidate',]))
            ->insert(FactoryHttpHeader::of(['Pragma'        => 'no-cache',]))
            ->insert(FactoryHttpHeader::of(['Expires'       => '0',]))
            ->insert(FactoryHttpHeader::of(['X-Count'    => $count]))
        ;

        return [
            ListenerDispatch::RESULT_DISPATCH => $response
        ];
    }
}
