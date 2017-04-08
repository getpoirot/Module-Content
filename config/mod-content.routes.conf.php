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
                    // 24 is length of content_id by persistence
                    'criteria' => '/posts/:content_id{{\w{24}}}',
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
                                \Module\Content\Actions\IOC::bareService()->ListPostsOfMeAction,
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

                    ## GET /posts/liked
                    #- Get the list of recent media liked by the owner.
                    'liked' => [
                        'route'   => 'RouteSegment',
                        'options' => [
                            'criteria'    => '/liked',
                            'match_whole' => true,
                        ],
                        'params'  => [
                            ListenerDispatch::CONF_KEY => [
                                \Module\Content\Actions\IOC::bareService()->ListPostsWhichUserLikedAction,
                            ],
                        ],
                    ],

                    'delegate' => [
                        'route' => 'RouteSegment',
                        'options' => [
                            // 24 is length of content_id by persistence
                            'criteria'    => '/:content_id{{\w{24}}}',
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
                                                \Module\Content\Actions\IOC::bareService()->ListPostLikesAction,
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
                                                \Module\Content\Actions\IOC::bareService()->LikePostAction,
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
                                                \Module\Content\Actions\IOC::bareService()->UnLikePostAction,
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
                                                \Module\Content\Actions\IOC::bareService()->ListCommentsOfPostAction,
                                            ],
                                        ],
                                    ],
                                    ## Write a Comment on The Post
                                    #- Add a comment on the post by the currently authenticated user.
                                    'create' => [
                                        'route'   => 'RouteMethod',
                                        'options' => [
                                            'method' => 'POST',
                                        ],
                                        'params'  => [
                                            ListenerDispatch::CONF_KEY => [
                                                \Module\Content\Actions\IOC::bareService()->AddCommentOnPostAction,
                                            ],
                                        ],
                                    ],
                                    ## Delete Comment From Post By Me Or Ignored By Content Owner
                                    #-
                                    'remove' => [
                                        'route'   => 'RouteMethodSegment',
                                        'options' => [
                                            // 24 is length of content_id by persistence
                                            'criteria' => '/:comment_id{{\w{24}}}',
                                            'method' => 'DELETE',
                                        ],
                                        'params'  => [
                                            ListenerDispatch::CONF_KEY => [
                                                \Module\Content\Actions\IOC::bareService()->RemoveCommentFromPostAction,
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
                    'criteria' => '/[@:username{{\w+}}][-:userid{{\w+}}]',
                    'match_whole' => false,
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
                                \Module\Content\Actions\IOC::bareService()->ListPostsOfUserAction,
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
                                \Module\Content\Actions\IOC::bareService()->BrowsePostsAction,
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
                                    die(sprintf('Implement Discover ...'));
                                },
                            ],
                        ],
                    ],
                ], // end users routes
            ],

        ], // end content routes

    ],];
