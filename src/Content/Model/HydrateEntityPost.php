<?php
namespace Module\Content\Model;

use Module\Content\Interfaces\Model\Entity\iEntityPost;
use Module\Content\Interfaces\Model\Entity\iEntityPostContentObject;
use Module\Content\Lib\FactoryContentObject;
use Module\Content\Model\Entity\EntityPost;
use Module\Content\Model\Entity\EntityPost\GeoObject;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\Std\ConfigurableSetter;
use Poirot\Std\Hydrator\HydrateGetters;


class HydrateEntityPost
    extends ConfigurableSetter
    implements \IteratorAggregate
    , iEntityPost
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
     * Construct
     *
     * @param array|\Traversable $options
     * @param array|\Traversable $defaults
     */
    function __construct($options = null, $defaults = null)
    {
        if ($defaults !== null)
            $this->with( static::parseWith($defaults) );

        parent::__construct($options);
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
     * Get Content Unique Identifier
     *
     * @return mixed
     */
    function getUid()
    {
        // Not Implemented
    }

    /**
     * Get Key/Value Content
     *
     * @return iEntityPostContentObject
     */
    function getContent()
    {
        $contentType   = ($this->_contentType) ? $this->_contentType : EntityPost\ContentObjectPlain::CONTENT_TYPE;
        $contentObject = FactoryContentObject::of($contentType, $this->content);
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

    /**
     * Get Post Stat
     * values: publish|draft|locked
     *
     * @return string
     */
    function getStat()
    {
        // TODO: Implement getStat() method.
    }

    /**
     * Get Date Time Created
     *
     * @return \DateTime
     */
    function getDateTimeCreated()
    {
        // TODO: Implement getDateTimeCreated() method.
    }

    // Implement Configurable

    /**
     * @inheritdoc
     *
     * @param array|\Traversable|iHttpRequest $optionsResource
     * @param array       $_
     *        usually pass as argument into ::with if self instanced
     *
     * @throws \InvalidArgumentException if resource not supported
     * @return array
     */
    static function parseWith($optionsResource, array $_ = null)
    {
        if (!static::isConfigurableWith($optionsResource))
            throw new \InvalidArgumentException(sprintf(
                'Invalid Configuration Resource provided; given: (%s).'
                , \Poirot\Std\flatten($optionsResource)
            ));


        // ..
        if ($optionsResource instanceof iHttpRequest)
            # Parse and assert Http Request
            $optionsResource = ParseRequestData::_($optionsResource)->parseBody();

        return parent::parseWith($optionsResource);
    }

    /**
     * Is Configurable With Given Resource
     *
     * @param mixed $optionsResource
     *
     * @return boolean
     */
    static function isConfigurableWith($optionsResource)
    {
        return $optionsResource instanceof iHttpRequest || parent::isConfigurableWith($optionsResource);
    }


    // Implement IteratorAggregate

    /**
     * @ignore
     *
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return \Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        $hydrator = new HydrateGetters($this);
        $hydrator->setExcludeNullValues();

        return $hydrator;
    }
}
