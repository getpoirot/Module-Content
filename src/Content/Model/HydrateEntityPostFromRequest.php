<?php
namespace Module\Content\Model;

use Module\Content\Interfaces\Model\Entity\iEntityPostContentObject;
use Module\Content\Lib\FactoryContentObject;
use Module\Content\Model\Entity\EntityPost;
use Module\Content\Model\Entity\EntityPost\ContentObjectGeneral;
use Module\Content\Model\Entity\EntityPost\GeoObject;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\Std\Struct\aDataOptions;
use Poirot\Std\Struct\Exceptions\exSetterMismatch;


class HydrateEntityPostFromRequest
    extends aDataOptions
{
    const FIELD_CONTENT_TYPE = 'content_type';
    const FIELD_CONTENT      = 'content';
    const FIELD_LOCATION     = 'location';
    const FIELD_STAT         = 'stat';
    const FIELD_SHARE        = 'share';
    const FIELD_HAS_COMMENT  = 'is_comment_enabled';


    protected $_contentType;
    protected $location;
    protected $content;
    protected $stat;
    protected $share;


    /**
     * HydrateEntityPostFromRequest constructor.
     * @param iHttpRequest $httpRequest
     */
    function __construct(iHttpRequest $httpRequest)
    {
        # Parse and assert Http Request
        $_post = ParseRequestData::_($httpRequest)->parseBody();
        $_post = $this->_assertCreatePostInputData($_post);

        try {
            $this->import($_post);
        } catch (exSetterMismatch $e) {
            // Filter Unknown Field That Post and Let It Play...
        }
    }


    // Setter Options:

    function setContentType($_contentType)
    {
        $this->_contentType = (string) $_contentType;
    }

    function setContent($content)
    {
        $this->content = $content;
    }

    function setLocation($location)
    {
        $this->location = $location;
    }

    function setStat($stat)
    {
        $this->stat = $stat;
    }

    function setShare($share)
    {
        $this->share = $share;
    }


    // Hydration Getters:
    // .. defined as tEntityPostGetter

    /**
     * Get Key/Value Content
     *
     * @return iEntityPostContentObject
     */
    function getContent()
    {
        $contentType   = ($this->_contentType) ? $this->_contentType : EntityPost\ContentObjectPlain::CONTENT_TYPE;

        $contentObject = FactoryContentObject::of($contentType);
        $contentObject->with($contentObject::parseWith($this->content));
        if (!$contentObject->isFulfilled())
            throw new \InvalidArgumentException(sprintf(
                'Content With Type (%s) not Fulfilled with given content.'
                , $contentType
            ), 400);

        return $contentObject;
    }

    /**
     * Get Geo Location
     *
     * @return GeoObject|null
     */
    function getLocation()
    {
        // Geo Location
        if (!is_null($this->location) && !$this->location instanceof GeoObject) {
            $location = new GeoObject;
            $location->setCaption($this->location['caption']);
            $location->setGeo($this->location['geo']);
            $this->location = $location;
        }

        return $this->location;
    }

    /**
     * Get Post Stat
     * values: publish|draft|locked
     *
     * @return string
     */
    function getStat()
    {
        ($this->stat) ?: $this->stat = EntityPost::STAT_PUBLISH;
        return $this->stat;
    }

    /**
     * Get Share Stat
     * values: public|private
     *
     * @return string
     */
    function getStatShare()
    {
        // Share
        ($this->share) ?: $this->share = EntityPost::STAT_SHARE_PUBLIC;
        return $this->share;
    }

    // ...

    protected function _assertCreatePostInputData(array $data)
    {
        // Check For Available Post Type
         ( isset($data[self::FIELD_CONTENT_TYPE]) && $data[self::FIELD_CONTENT_TYPE] )
             ?: $data[self::FIELD_CONTENT_TYPE] = ContentObjectGeneral::CONTENT_TYPE;

        // Inject Content
        if (!isset($data[self::FIELD_CONTENT]))
            throw new \InvalidArgumentException('Content Object is Required.', 400);


        return $data;
    }
}
