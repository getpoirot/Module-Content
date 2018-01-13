<?php
namespace Module\Content\Actions\Posts;

use Module\Content\Actions\aAction;
use Module\Content\Interfaces\Model\Repo\iRepoPosts;
use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;
use Poirot\Std\Exceptions\exUnexpectedValue;
use Poirot\Http\HttpResponse;
use Poirot\Http\Header\FactoryHttpHeader;


/**
 * Respond With Count Of New Post(s) after specified post by id
 *
 */
class NewPostsCountAction
    extends aAction
{
    /** @var iRepoPosts */
    protected $repoPosts;


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
        $pReq = ParseRequestData::_($this->request)->parseQueryParams();
        if ( ! isset($pReq['since']) )
            throw exUnexpectedValue::paramIsRequired('since');

        $postId = trim($pReq['since']);
        $count  = $this->repoPosts->countNewPostsAfter($postId);

        return $this->_respond($count);
    }


    // ..

    private function _respond($count)
    {
        # Build Response
        #
        $response = new HttpResponse;
        $response->headers()
            ->insert(FactoryHttpHeader::of(['Cache-Control' => 'no-cache, no-store, must-revalidate',]))
            ->insert(FactoryHttpHeader::of(['Pragma'        => 'no-cache',]))
            ->insert(FactoryHttpHeader::of(['Expires'       => '0',]))
            ->insert(FactoryHttpHeader::of(['X-Count'       => $count]))
        ;

        return [
            ListenerDispatch::RESULT_DISPATCH => $response
        ];
    }
}
