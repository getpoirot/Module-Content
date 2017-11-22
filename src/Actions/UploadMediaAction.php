<?php
namespace Module\Content\Actions;

use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Poirot\ApiClient\AccessTokenObject;
use Poirot\ApiClient\TokenProviderSolid;
use Poirot\Http\HttpMessage\Request;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;
use Poirot\Psr7\UploadedFile;
use Poirot\Std\Exceptions\exUnexpectedValue;
use Poirot\TenderBinClient\Client;


class UploadMediaAction
    extends aAction
{
    /** @var Client */
    protected $storage;


    /**
     * UploadMediaAction constructor.
     *
     * @param Client       $storageClient @IoC /module/tenderBinClient/services/ClientTender
     * @param iHttpRequest $httpRequest
     */
    function __construct(Client $storageClient, iHttpRequest $httpRequest)
    {
        $this->storage = $storageClient;

        parent::__construct($httpRequest);
    }


    /**
     * Upload Temporary Media Into Storage
     *
     * @param iAccessToken $token
     *
     * @return array
     * @throws \Exception
     */
    function __invoke($token = null)
    {
        # Assert Token
        #
        $this->assertTokenByOwnerAndScope($token);


        ## Parse Request Body
        #
        $request = Request\Plugin\ParseRequestData::_($this->request)->parseBody();


        ## Validate Uploaded Media
        #
        if (! isset($request['media']) )
            throw exUnexpectedValue::paramIsRequired('media');


        /** @var UploadedFile $media */
        if (! ( $media = $request['media'] ) instanceof UploadedFile)
            throw new exUnexpectedValue('Media must be uploaded file.');


        ## Store Image Into Object Storage
        #
        $r      = $this->_storeMedia($media, $token);
        $binArr = $r['bindata'];

        return [
            ListenerDispatch::RESULT_DISPATCH => $binArr,
        ];
    }


    // ..

    /**
     * Store Uploaded File Into Object Storage
     *
     * @param UploadedFile $media
     * @param iAccessToken $token
     *
     * @return array
     */
    private function _storeMedia($media, $token)
    {
        // Request Behalf of User as Owner With Token
        $this->storage->setTokenProvider(new TokenProviderSolid(
            new AccessTokenObject(['access_token' => $token->getIdentifier()])
        ));

        $r = $this->storage->store(
            fopen($media->getTmpName(), 'rb')
            , null
            , $media->getClientFilename()
            , [
                '_segment'         => 'contents',
                '__after_created'  => '{ "versions":[{ 
                  "thumb":     {"optimage": {"type": "crop",   "size": "200x200" , "q": 80}}, 
                  "low_thumb": {"optimage": {"type": "crop",   "size": "200x200" , "q": 10}}, 
                  "small":     {"optimage": {"type": "resize", "size": "400x700", "q": 80}}, 
                  "low_small": {"optimage": {"type": "resize", "size": "400x700", "q": 10}}, 
                  "large":     {"optimage": {"type": "resize", "size": "800x1400", "q": 80}}, 
                  "low_large": {"optimage": {"type": "resize", "size": "800x1400", "q": 10}}
                }]}',
            ]
            , 360 // expiration time
            , false );


        return $r;
    }
}
