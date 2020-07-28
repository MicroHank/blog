<?php
	namespace App\Repositories\Line;

	use Illuminate\Support\Facades\DB;

	class LineChatbotMessageRepository
	{
		public function __construct() {}

        /**
         * 回覆訊息給 Line User
         *
         * @param Array $flex_messages: Flex Message
         * @param String $replyToken: Callback 取得的 replyToken 參數
         */
        public function sendMessage($flex_messages, $replyToken)
        {
            $data = [
                'replyToken' => $replyToken,
                'messages' => $flex_messages,
            ] ;

            // Reply API
            $ch = curl_init() ;
            curl_setopt($ch, CURLOPT_URL, 'https://api.line.me/v2/bot/message/reply') ;
            curl_setopt($ch, CURLOPT_POST, true) ;
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ;
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)) ;
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' .config('line.channel_access_token')
            ]) ;
            $result = curl_exec($ch) ;
            curl_close($ch) ;
        }

		/**
         * 製作 Flex Message
         *
         * @param String message: 要回覆給 User 的訊息字串
         * @return Array Flex Message
         */
		public function getFlexMessage($message)
		{
			$github_uri = config('henwen.github_uri') ;
			$github_image = config('henwen.github_image') ;

			return $flex_messages = [
                [
                    // 回覆基本文字訊息使用參數
                    // 'type' => 'text', 
                    // 'text' => $message,

                    // 回覆 flex 訊息
                    'type' => 'flex',
                    'altText' => 'This is a Flex Message',
                    'contents' => [
                        'type' => 'bubble',
                        'styles' => [
	                    	'header' => [
	                    		'backgroundColor' => '#92b7d1',
	                    	],
	                    	'body' => [
	                    		'backgroundColor' => '#d3deed',
	                    	],
                            'footer' => [
                                'backgroundColor' => '#dfe9f0',
                            ],
	                    ],
	                    // 'hero' => [
	                    // 	'type' => 'image',
	                    // 	'url' => $github_image,
	                    // 	'size' => 'full',
	                    // 	// 'aspectRatio' => '2:1',
	                    // 	'aspectMode' => 'cover',
	                    // 	'action' => [
	                    // 		'type' => 'uri',
	                    // 		'uri' => $github_uri,
	                    // 	],
	                    // ],
                        'header' => [
                            'type' => 'box',
                            'layout' => 'vertical',
                            'contents' => [
                                [
                                    'type' => 'text',
                                    'text' => '回傳結果',
                                    'color' => '#446278',
                                    'size' => 'xl',
                                    'margin' => 'sm',
                                ],
                            ],
                        ],
                        'body' => [
                            'type' => 'box',
                            'layout' => 'vertical',
                            'contents' => [
                            	[
                            		'type' => 'box',
                            		'layout' => 'horizontal',
                            		'contents' => [
                            			[
		                                    'type' => 'button',
		                                    'style' => 'primary',
		                                    'height' => 'sm',
		                                    'action' => [
		                                    	'type' => 'uri',
		                                    	'label' => 'Github',
		                                    	'uri' => $github_uri,
		                                    ],
		                                    'flex' => 2,
		                                ],
                            			[
                            				'type' => 'text',
                            				'text' => 'Henwen\'s Laravel',
                            				'gravity' => 'center',
		                            		'weight' => 'bold',
		                            		'margin' => 'sm',
		                            		'flex' => 3,
                            			],
                            		],
                            	],
                            	[
                            		'type' => 'box',
                            		'layout' => 'baseline',
                            		'margin' => 'md',
                            		'contents' => [
                            			[
                            				'type' => 'icon',
                            				'size' => 'sm',
                            				'url' => 'https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gold_star_28.png',
                            			],
                            			[
                            				'type' => 'icon',
                            				'size' => 'sm',
                            				'url' => 'https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gold_star_28.png',
                            			],
                            			[
                            				'type' => 'icon',
                            				'size' => 'sm',
                            				'url' => 'https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gold_star_28.png',
                            			],
                            			[
                            				'type' => 'icon',
                            				'size' => 'sm',
                            				'url' => 'https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gold_star_28.png',
                            			],
                            			[
                            				'type' => 'icon',
                            				'size' => 'sm',
                            				'url' => 'https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gold_star_28.png',
                            			],
                            			[
                            				'type' => 'text',
                            				'text' => '5.0',
                            				'size' => 'sm',
                            				'margin' => 'md',
                            				'flex' => 0,
                            				'color' => '#ccaabb',
                            			],
                            		],
                            	],
                                [
                                    'type' => 'text',
                                    'text' => $message,
                                    'wrap' => true,
                                ],
                            ],
                        ],
                        'footer' => [
                        	'type' => 'box',
                            'layout' => 'vertical',
                            'contents' => [
                                [
                                    'type' => 'button',
                                    'action' => [
                                    	'type' => 'postback',
                                    	'label' => 'Postback',
                                    	'data' => 'action=test&param1=111&param2=222',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ] ;
		}

		
	}