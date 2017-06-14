<?php
namespace Module\Content\Services;

use Poirot\Application\aSapi;
use Poirot\Ioc\Container\Service\aServiceContainer;
use Poirot\OAuth2\Interfaces\Server\Repository\iOAuthClient;
use Poirot\OAuth2Client\Federation\TokenProvider\TokenFromOAuthClient;
use Poirot\OAuth2Client\Grant\Container\GrantPlugins;
use Poirot\Std\Struct\DataEntity;
use Poirot\TenderBinClient\Client;


class ServiceClientTender
    extends aServiceContainer
{
    const CONF = 'client_tender';

    /** @var string Service Name */
    protected $name = 'clientTender';


    /**
     * Create Service
     *
     * @return Client
     */
    function newService()
    {
        $conf      = $this->_attainConf();

        $serverUrl = $conf['server_url'];

        /** @var \Poirot\OAuth2Client\Client $oauthClient */
        $oauthClient = $this->services()->get('/module/OAuth2Client/services/OAuthClient');
        $c = new Client(
            $serverUrl
            , new TokenFromOAuthClient($oauthClient, $oauthClient->withGrant(GrantPlugins::CLIENT_CREDENTIALS) )
        );

        return $c;
    }


    // ..

    /**
     * Attain Merged Module Configuration
     * @return array
     */
    protected function _attainConf()
    {
        $sc     = $this->services();
        /** @var aSapi $sapi */
        $sapi   = $sc->get('/sapi');
        /** @var DataEntity $config */
        $config = $sapi->config();
        $config = $config->get(\Module\Content\Module::CONF);

        $r = array();
        if (is_array($config) && isset($config[static::CONF]))
            $r = $config[static::CONF];

        return $r;
    }
}
