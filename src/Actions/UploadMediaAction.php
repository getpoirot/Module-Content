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
        set_time_limit(0);

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
                // TODO
                'storage_type' => FactoryMediaObject::STORAGE_TYPE,
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
     * @throws \Exception
     */
    private function _storeMedia($media, $token)
    {
        $handler     = FactoryMediaObject::hasHandlerOfStorage(FactoryMediaObject::STORAGE_TYPE);
        $c = $handler->client();

        // Request Behalf of User as Owner With Token
        $c->setTokenProvider(new TokenProviderSolid(
            new AccessTokenObject(['access_token' => $token->getIdentifier()])
        ));

        /*
        $filePath = $media->getTmpName();
        $newFile  = sys_get_temp_dir().'/'.basename($filePath).'_'.$media->getClientFilename();
        if (! copy($filePath, $newFile) )
            throw new \Exception('Error Copying File...');
        */

        $r = $c->store(
            $media
            , null
            , $media->getClientFilename()
            , [
                '_segment'         => 'contents',
                '__after_created'  => '{ "mime-type": {
                   "types": [
                     "image/*"
                   ],
                   "then": {
                     "versions":[{ 
                          "thumb":     {"optimage": {"type": "crop",   "size": "200x200" , "q": 80}}, 
                          "low_thumb": {"optimage": {"type": "crop",   "size": "200x200" , "q": 10}}, 
                          "small":     {"optimage": {"type": "resize", "size": "400x700", "q": 80}}, 
                          "low_small": {"optimage": {"type": "resize", "size": "400x700", "q": 10}}, 
                          "large":     {"optimage": {"type": "resize", "size": "800x1400", "q": 80}}, 
                          "low_large": {"optimage": {"type": "resize", "size": "800x1400", "q": 10}}
                    }]
                   }
                 }
               }',
            ]
            , 360 // expiration time
            , false );


//        unlink($newFile);

        return $r;
    }
}
