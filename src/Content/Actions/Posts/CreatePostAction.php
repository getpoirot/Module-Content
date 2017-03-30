<?php
namespace Module\Content\Actions\Posts;

use Module\Content;
use Module\Content\Actions\aAction;
use Module\Content\Interfaces\Model\Repo\iRepoPosts;
use Poirot\Application\Sapi\Server\Http\ListenerDispatch;
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
     *
     * @return Content\Model\Entity\EntityPost
     */
    function __invoke(iHttpRequest $request = null, iEntityAccessToken $token = null)
    {
        if (!$request instanceof iHttpRequest)
            throw new \RuntimeException('Cant attain Http Request Object.');


        # Validate Access Token
        \Module\OAuth2Client\validateGivenToken($token, (object) ['mustHaveOwner' => true, 'scopes' => [] ]);


        # Create Post Entity From Http Request
        $entityPost = new Content\Model\Entity\EntityPost(
            new Content\Model\HydrateEntityPostFromRequest($request)
        );

        // Determine Owner Identifier From Token
        $entityPost->setOwnerIdentifier($token->getOwnerIdentifier());


        # Persist Post Entity
        $persistPost = $this->CreatePost($entityPost);
        return $persistPost;
    }

    static function closureMakeResponseResult()
    {
        return function(Content\Model\Entity\EntityPost $post = null) {
            return [
                ListenerDispatch::RESULT_DISPATCH => [
                    '$post' => [
                        'uid'        => (string) $post->getUid(),
                        'content'    => $post->getContent(),
                        'stat'       => $post->getStat(),
                        'stat_share' => $post->getStatShare(),
                        'location'   => [
                            'caption' => $post->getLocation()->getCaption(),
                            'geo'     => [
                                'lon' => $post->getLocation()->getGeo('lon'),
                                'lat' => $post->getLocation()->getGeo('lat'),
                            ],
                        ],
                        'datetime_created' => [
                            '$datetime' => $post->getDateTimeCreated(),
                        ],
                        'owner_identifier' => (string) $post->getOwnerIdentifier(),
                    ],
                ],
            ];
        };
    }
}
