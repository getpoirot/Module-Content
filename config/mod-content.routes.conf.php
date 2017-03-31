<?php
use Poirot\Application\Sapi\Server\Http\ListenerDispatch;

return
    [ 'content'  => [
        'route' => 'RouteSegment',
        'options' => [
            'criteria'    => '',
            'match_whole' => false,
        ],
        'params'  => [
            ListenerDispatch::CONF_KEY => [
                // This Action Run First In Chains and Assert Validate Token
                //! define array allow actions on matched routes chained after this action
                /*
                 * [
                 *    [0] => Callable Defined HERE
                 *    [1] => routes defined callable
                 *     ...
                 */
                \Module\OAuth2Client\Actions\IOC::bareService()->AssertToken,
            ],
        ],


        'routes' => [

            ## GET /posts/{{post_id}}
            #- get a post consider access privacy.
            'get_post' => [
                'route'   => 'RouteMethodSegment',
                'options' => [
                    'criteria' => '/posts/:content_id{\w+}',
                    'method'   => 'GET',
                    'match_whole' => true,
                ],
                'params'  => [
                    ListenerDispatch::CONF_KEY => [
                        \Module\Content\Actions\IOC::bareService()->RetrievePostAction,
                    ],
                ],
            ],

            ## /posts
            'posts' => [
                'route' => 'RouteSegment',
                'options' => [
                    'criteria'    => '/posts',
                    'match_whole' => false,
                ],
                'routes' =>
                [
                    ## GET /posts
                    #- Used to retrieve owner content(s) by meta attributes.
                    'list' => [
                        'route'   => 'RouteMethodSegment',
                        'options' => [
                            'criteria'    => '/',
                            'method'      => 'GET',
                            'match_whole' => true,
                        ],
                        'params'  => [
                            ListenerDispatch::CONF_KEY => [
                                function() {
                                    kd('List and Filter Post ');
                                },
                            ],
                        ],
                    ],

                    ## POST /posts
                    #- Create a Post Content by Currently Authenticated User.
                    'create' => [
                        'route'   => 'RouteMethodSegment',
                        'options' => [
                            'criteria'    => '/',
                            'method'      => 'POST',
                            'match_whole' => true,
                        ],
                        'params'  => [
                            ListenerDispatch::CONF_KEY => [
                                \Module\Content\Actions\IOC::bareService()->CreatePostAction,
                            ],
                        ],
                    ],

                    'delegate' => [
                        'route' => 'RouteSegment',
                        'options' => [
                            'criteria'    => '/:content_id{\w+}',
                            'match_whole' => false,
                        ],
                        'routes' => [

                            ## PUT /posts/{{post_id}}
                            #- Update a post that currently authenticated user has access to edit it.
                            'edit' => [
                                'route'   => 'RouteMethodSegment',
                                'options' => [
                                    'criteria'    => '/',
                                    'method'      => 'PUT',
                                    'match_whole' => true,
                                ],
                                'params'  => [
                                    ListenerDispatch::CONF_KEY => [
                                        \Module\Content\Actions\IOC::bareService()->EditPostAction,
                                    ],
                                ],
                            ],

                            ## DELETE /posts/{{post_id}}
                            #- Delete a post by currently authenticated user.
                            'delete' => [
                                'route'   => 'RouteMethodSegment',
                                'options' => [
                                    'criteria'    => '/',
                                    'method'      => 'DELETE',
                                    'match_whole' => true,
                                ],
                                'params'  => [
                                    ListenerDispatch::CONF_KEY => [
                                        \Module\Content\Actions\IOC::bareService()->DeletePostAction,
                                    ],
                                ],
                            ],

                            ## /posts/{{content_id}}/likes
                            'likes' => [
                                'route' => 'RouteSegment',
                                'options' => [
                                    'criteria'    => '/likes',
                                    'match_whole' => true,
                                ],
                                'routes' => [
                                    ## List Users who have liked a Post
                                    'list' => [
                                        'route'   => 'RouteMethod',
                                        'options' => [
                                            'method' => 'GET',
                                        ],
                                        'params'  => [
                                            ListenerDispatch::CONF_KEY => [
                                                function($content_id = null) {
                                                    kd(sprintf('(%s) List Likes.', $content_id));
                                                },
                                            ],
                                        ],
                                    ],
                                    ## Set Like on Post
                                    #- Set a like on the post by the currently authenticated user.
                                    'like' => [
                                        'route'   => 'RouteMethod',
                                        'options' => [
                                            'method' => 'POST',
                                        ],
                                        'params'  => [
                                            ListenerDispatch::CONF_KEY => [
                                                function($content_id = null) {
                                                    kd(sprintf('(%s) Set Likes On Post', $content_id));
                                                },
                                            ],
                                        ],
                                    ],
                                    ## Set Like on Post
                                    #- Set a like on the post by the currently authenticated user.
                                    'unlike' => [
                                        'route'   => 'RouteMethod',
                                        'options' => [
                                            'method' => 'DELETE',
                                        ],
                                        'params'  => [
                                            ListenerDispatch::CONF_KEY => [
                                                function($content_id = null) {
                                                    kd(sprintf('(%s) Un-Likes The Post', $content_id));
                                                },
                                            ],
                                        ],
                                    ],
                                ], // end likes routes
                            ], // end likes

                            ## /posts/{{content_id}}/comments
                            'comments' => [
                                'route' => 'RouteSegment',
                                'options' => [
                                    'criteria'    => '/comments',
                                    'match_whole' => false,
                                ],
                                'routes' => [
                                    ## List Users who have liked a Post
                                    'list' => [
                                        'route'   => 'RouteMethod',
                                        'options' => [
                                            'method' => 'GET',
                                        ],
                                        'params'  => [
                                            ListenerDispatch::CONF_KEY => [
                                                function($content_id = null) {
                                                    kd(sprintf('(%s) List Comments.', $content_id));
                                                },
                                            ],
                                        ],
                                    ],
                                    ## Set Like on Post
                                    #- Set a like on the post by the currently authenticated user.
                                    'create' => [
                                        'route'   => 'RouteMethod',
                                        'options' => [
                                            'method' => 'POST',
                                        ],
                                        'params'  => [
                                            ListenerDispatch::CONF_KEY => [
                                                function($content_id = null) {
                                                    kd(sprintf('(%s) Create Comment On Post', $content_id));
                                                },
                                            ],
                                        ],
                                    ],
                                    ## Set Like on Post
                                    #- Set a like on the post by the currently authenticated user.
                                    'remove' => [
                                        'route'   => 'RouteMethodSegment',
                                        'options' => [
                                            'criteria' => '/:comment_id{\w+}',
                                            'method' => 'DELETE',
                                        ],
                                        'params'  => [
                                            ListenerDispatch::CONF_KEY => [
                                                function($content_id = null, $comment_id = null) {
                                                    kd(sprintf('Delete Comment(%s) Of The Post(%s)', $comment_id, $content_id));
                                                },
                                            ],
                                        ],
                                    ],
                                ], // end likes routes
                            ], // end likes

                        ], // end post delegate routes
                    ], // end post delegate

                ], // end posts route
            ], // end posts

            ## Users
            'users' => [
                'route'   => 'RouteSegment',
                'options' => [
                    'criteria' => '/[@:username{\w+}][-:userid{\w+}]',
                    'match_whole' => false,
                    'params'  => [
                        ListenerDispatch::CONF_KEY => [
                            function($username = null, $userid = null) {
                                k(sprintf('Lists Post of @(%s)#(%s)', $username, $userid));
                                if ($userid === null)
                                    // Find userid by username
                                    return ['userid' => '#userid'];
                            },
                        ],
                    ],
                ],
                'routes' => [
                    ## /@username/posts
                    #- Used to retrieve user`s posts by meta attributes.
                    'list_posts' => [
                        'route'   => 'RouteMethodSegment',
                        'options' => [
                            'criteria' => '/posts',
                            'method'   => 'GET',
                            'match_whole' => true,
                        ],
                        'params'  => [
                            ListenerDispatch::CONF_KEY => [
                                function($username = null, $userid = null) {
                                    kd(sprintf('Lists Post of #(%s)', $userid));
                                },
                            ],
                        ],
                    ],
                ], // end users routes
            ], // end users

            ## Browse
            'browse' => [
                'route'   => 'RouteSegment',
                'options' => [
                    'criteria' => '/browse',
                    'match_whole' => false,
                ],
                'params'  => [
                    ListenerDispatch::CONF_KEY => [

                    ],
                ],
                'routes' => [
                    ## /browse
                    #- Suggest Authorized? user posts stream to explore.
                    'explore' => [
                        'route'   => 'RouteMethodSegment',
                        'options' => [
                            'criteria' => '/',
                            'method'   => 'GET',
                            'match_whole' => true,
                        ],
                        'params'  => [
                            ListenerDispatch::CONF_KEY => [
                                function() {
                                    kd(sprintf('Browse'));
                                },
                            ],
                        ],
                    ],
                    ## /browse/discover
                    #- Suggest Authorized? user posts stream to explore.
                    'discover' => [
                        'route'   => 'RouteMethodSegment',
                        'options' => [
                            'criteria' => '/discover',
                            'method'   => 'GET',
                            'match_whole' => true,
                        ],
                        'params'  => [
                            ListenerDispatch::CONF_KEY => [
                                function() {
                                    kd(sprintf('Discover'));
                                },
                            ],
                        ],
                    ],
                ], // end users routes
            ],

        ], // end content routes

    ],];
