<?php
namespace Module\Content\Model\PostContentObject;

use Module\Content\Interfaces\Model\Entity\iEntityPostContentObject;
use Poirot\Std\Struct\DataOptionsOpen;


class PlainContentObject
    extends DataOptionsOpen
    implements iEntityPostContentObject
{
    const CONTENT_TYPE = 'plain';

    /** @var string */
    protected $description;


    /**
     * Get Content Type
     *
     * @return string
     */
    function getContentType()
    {
        return self::CONTENT_TYPE;
    }

    /**
     * Set Post Description
     *
     * @param string $text
     *
     * @return $this
     */
    function setDescription($text)
    {
        $this->description = (string) $text;
        return $this;
    }

    /**
     * Get Description
     * @required
     *
     * @return string
     */
    function getDescription()
    {
        return $this->description;
    }


    // ...

    /**
     * Build Object With Provided Options
     *
     * @param array|\Traversable $options Associated Array
     * @param bool $throwException Throw Exception On Wrong Option
     *
     * @return $this
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    function with($options, $throwException = false)
    {
        $this->import($options);
    }

    /**
     * Load Build Options From Given Resource
     *
     * - usually it used in cases that we have to support
     *   more than once configure situation
     *   [code:]
     *     Configurable->with(Configurable::withOf(path\to\file.conf))
     *   [code]
     *
     * !! With this The classes that extend this have to
     *    implement desired parse methods
     *
     * @param array|mixed $optionsResource
     * @param array $_
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


        return $optionsResource;
    }

    /**
     * Is Configurable With Given Resource
     * @ignore
     *
     * @param mixed $optionsResource
     *
     * @return boolean
     */
    static function isConfigurableWith($optionsResource)
    {
        return is_array($optionsResource);
    }
}
