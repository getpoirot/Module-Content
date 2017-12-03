<?php
namespace Module\Content\Actions;

use Module\Apanaj\Storage\HandleIrTenderBin;
use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Poirot\ApiClient\AccessTokenObject;
use Poirot\ApiClient\TokenProviderSolid;
use Poirot\Http\HttpMessage\Request;
use Poirot\OAuth2Client\Interfaces\iAccessToken;
use Poirot\Psr7\UploadedFile;
use Poirot\Std\Exceptions\exUnexpectedValue;
use Poirot\TenderBinClient\FactoryMediaObject;


class UploadMediaAction
    extends aAction
{
//    const STORAGE_TYPE = HandleIrTenderBin::STORAGE_TYPE;
    const STORAGE_TYPE = 'tenderbin';


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
            ListenerDispatch::RESULT_DISPATCH => [
                'storage_type' => self::STORAGE_TYPE,
                'hash'         => $binArr['hash'],
                'content_type' => $binArr['content_type'],
            ],
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
        $storageType = self::STORAGE_TYPE;
        $handler     = FactoryMediaObject::hasHandlerOfStorage($storageType);


        $c = $handler->client();

        // Request Behalf of User as Owner With Token
        $c->setTokenProvider(new TokenProviderSolid(
            new AccessTokenObject(['access_token' => $token->getIdentifier()])
        ));

        $r = $c->store(
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
