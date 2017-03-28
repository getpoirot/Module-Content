<?php
namespace Module\Content\Services;

use Module\Content\Interfaces\Model\Entity\iEntityPostContentObject;
use Module\Content\Model\PostContentObject\GeneralContentObject;
use Module\Content\Model\PostContentObject\PlainContentObject;
use Poirot\Ioc\Container\aContainerCapped;
use Poirot\Ioc\Container\BuildContainer;
use Poirot\Ioc\Container\Exception\exContainerInvalidServiceType;
use Poirot\Ioc\Container\Service\ServicePluginLoader;
use Poirot\Loader\LoaderMapResource;


class ContainerCappedContentObject
    extends aContainerCapped
{
    protected $_map_resolver_options = [
        'plain'   => PlainContentObject::class,
        'general' => GeneralContentObject::class,
    ];


    /**
     * Construct
     *
     * @param BuildContainer $cBuilder
     *
     * @throws \Exception
     */
    function __construct(BuildContainer $cBuilder = null)
    {
        $this->_attachDefaults();

        parent::__construct($cBuilder);
    }

    /**
     * Validate Plugin Instance Object
     *
     * @param mixed $pluginInstance
     *
     * @throws exContainerInvalidServiceType
     * @return void
     */
    function validateService($pluginInstance)
    {
        if (!$pluginInstance instanceof iEntityPostContentObject)
            throw new exContainerInvalidServiceType('Invalid Plugin Of Content Object Provided.');

    }


    // ..

    protected function _attachDefaults()
    {
        $service = new ServicePluginLoader([
            'resolver_options' => [
                LoaderMapResource::class => $this->_map_resolver_options
            ],
        ]);

        $this->set($service);
    }
}
