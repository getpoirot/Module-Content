<?php
namespace Module\Content\Actions\Posts;

use Module\Content;
use Module\Content\Actions\aAction;
use Module\Content\Interfaces\Model\Repo\iRepoPosts;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityAccessToken;


class CreatePostAction
    extends aAction
{
    /** @var iRepoPosts */
    protected $repoPosts;

    
    /**
     * Create Post By Http Request
     * 
     * @param iHttpRequest|null       $request
     * @param iEntityAccessToken|null $token
     */
    function __invoke(iHttpRequest $request = null, iEntityAccessToken $token = null)
    {
        if (!$request instanceof iHttpRequest)
            throw new \RuntimeException('Cant attain Http Request Object.');


        # Validate Access Token
        \Module\OAuth2Client\validateGivenToken($token, (object) ['mustHaveOwner' => true, 'scopes' => [] ]);


        # Parse and assert Http Request
        $_post = ParseRequestData::_($request)->parseBody();
        $_post = $this->_assertCreatePostInputData($_post);

        print_r($_post);die;
    }
    
    protected function _assertCreatePostInputData(array $data)
    {
        # Validate Data

        // Check For Available Post Type
        $contentType = ($data['content_type'])
            ? $data['content_type']
            : Content\Model\PostContentObject\GeneralContentObject::CONTENT_TYPE;

        if (false === Content\Services\IOC::ContentObjectContainer()->has($contentType))
            throw new \InvalidArgumentException(sprintf(
                'Invalid Post Content-Type (%s).'
                , $contentType
            ), 400);


        // Inject Content
        if (!isset($data['content']))
            throw new \InvalidArgumentException('Content Object is Required.', 400);

        /** @var Content\Model\PostContentObject\PlainContentObject $contentObject */
        $contentObject = Content\Services\IOC::ContentObjectContainer()->get($contentType);
        $contentObject->with($contentObject::parseWith($data['content']));
        if (!$contentObject->isFulfilled())
            throw new \InvalidArgumentException(sprintf(
                'Content With Type (%s) not Fulfilled with given content.'
                , $contentType
            ), 400);

        $data['content'] = $contentObject;


        // Stat
        (isset($data['stat'])) ?: $data['stat'] = 'publish';

        // Share
        (isset($data['share'])) ?: $data['share'] = 'public';

        // Comment Enabled
        (isset($data['is_comment_enabled'])) ?: $data['is_comment_enabled'] = true;

        // Geo Location
        if (isset($data['location'])) {
            $location = new Content\Model\EntityPostGeoObject;
            $location->setCaption($data['location']['caption']);
            $location->setGeo($data['location']['geo']);
            $data['location'] = $location;
        }

        # Filter Data


        return $data;
    }
}
