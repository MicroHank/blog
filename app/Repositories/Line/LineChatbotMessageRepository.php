<?php
	namespace App\Repositories\Line;

	use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Log;
    use Carbon\Carbon;

	class LineChatbotMessageRepository
	{
		public function __construct() {}

        /**
         * 製作指令清單
         *
         * @return String $message Menu string
         */
        public function getMenu()
        {
            $message  = "指令 'member' 會員管理\n" ;
            $message .= "指令 'user' 使用者管理\n" ;
            $message .= "指令 'mail' 信件通知\n" ;
            $message .= "指令 'sql' 可執行 SQL 語法\n" ;
            $message .= "指令 'server' 查看主機狀態\n" ;
            $message .= "指令 'gold/黃金' 查看黃金價格\n" ;
            $message .= "指令 'movie/電影' 查看電影時刻表" ;
            return $message ;
        }

        /**
         * 製作 user 第二階段指令清單
         *
         * @return String $message Menu string
         */
        public function getUserMenu()
        {
            $message = "指令 'get' 取得會員\n" ;
            $message .= "指令 'delete' 刪除會員\n" ;
            $message .= "指令 'count' 會員總數" ;
            return $message ;
        }

        /**
         * 製作 member 第二階段指令清單
         *
         * @return String $message Menu string
         */
        public function getMemberMenu()
        {
            $message = "指令 'get' 取得會員\n" ;
            $message .= "指令 'delete' 刪除會員\n" ;
            $message .= "指令 'count' 會員總數" ;
            return $message ;
        }

        /**
         * 製作 server 第二階段指令清單
         *
         * @return String $message Menu string
         */
        public function getServerMenu()
        {
            $message = "指令 'disk' 查看硬碟空間\n" ;
            $message .="指令 'task' 查看行程狀態" ;
            return $message ;
        }

        /**
         * 製作 movie 第二階段指令清單
         *
         * @return String $message Menu string
         */
        public function getMovieMenu()
        {
            $message = "指令 '1' 查看 大遠百威秀 電影時段\n" ;
            $message .="指令 '2' 查看 新光影城 電影時段" ;
            return $message ;
        }

        /**
         * 製作 movie 時段字串
         *
         * @return String $message
         */
        public function getMovieMessage($url)
        {
            include_once base_path('public/phplib/simple_html_dom.php') ;

            $dom = file_get_html($url) ;
            $name = $dom->find('div[class=theaterlist_name] a') ;
            $photo = $dom->find('div[class=release_foto] img') ;
            $intro = $dom->find('div[class=theaterlist_name] a') ;
            $type = $dom->find('div[class=theaterlist_name] div div') ;
            $time = $dom->find('div[class=release_info_text] ul[class=theater_time]') ;

            $message = [] ;

            for ($i = 0 ; $i < count($name) ; $i++) {
                $movie = [] ;
                // 電影名稱與類型
                $movie["name"] = trim($name[$i]->innertext).' ('.trim($type[$i]->innertext).')' ;
                // 電影圖片連結
                $movie["photo"] = $photo[$i]->attr['src'] ;
                // 電影介紹連結
                $movie["intro"] = $intro[$i]->attr['href'] ;
                // 電影播放時間
                $time_dom = str_get_html($time[$i]) ;
                $a_content = $time_dom->find('a') ; // 一個 a tag 為一筆電影時間
                $time_string = [] ;
                // 最多 10 筆播放時間: API text 欄位長度有限制
                for ($t = 0 ; $t < (count($a_content) > 10 ? 10 : count($a_content)) ; $t++) {
                    array_push($time_string, trim($a_content[$t]->innertext)) ;
                }
                $movie["time"] = join(' ', $time_string) ;
                array_push($message, $movie) ;
            }
            return $message ;
        }

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
                                            'align' => 'center',
		                            		'weight' => 'bold',
		                            		'flex' => 3,
                            			],
                            		],
                            	],
                                [
                                    'type' => 'text',
                                    'text' => $message,
                                    'margin' => 'sm',
                                    'wrap' => true,
                                ],
                            ],
                        ],
                        'footer' => [
                            'type' => 'box',
                            'layout' => 'vertical',
                            'contents' => [
                                [
                                    'type' => 'text',
                                    'text' => 'WAMP + Laravel 5.4 @ 2020 / 07',
                                    'align' => 'center',
                                    'gravity' => 'center',
                                ],
                            ],
                        ],
                    ],
                ],
            ] ;
		} // End getFlexMessage()

        /**
         * 製作 Flex Message
         *
         * @param String gold: 金價一盎司/一公克的買進/賣出價格
         * @return Array Flex Message
         */
        public function getGoldFlexMessage($message)
        {
            $github_uri = config('henwen.github_uri') ;

            return $flex_messages = [
                [
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
                                            'align' => 'center',
                                            'weight' => 'bold',
                                            'flex' => 3,
                                        ],
                                    ],
                                ],
                                [
                                    'type' => 'box',
                                    'layout' => 'horizontal',
                                    'margin' => 'md',
                                    'backgroundColor' => '#7fb0ec',
                                    'contents' => [
                                        [
                                            'type' => 'text',
                                            'text' => '規格',
                                            'color' => '#FFFFFF',
                                            'flex' => 1,
                                        ],
                                        [
                                            'type' => 'text',
                                            'text' => '買進 (美金)',
                                            'color' => '#FFFFFF',
                                            'flex' => 2,
                                        ],
                                        [
                                            'type' => 'text',
                                            'text' => '賣出 (美金)',
                                            'color' => '#FFFFFF',
                                            'flex' => 2,
                                        ],
                                    ],
                                ],
                                [
                                    'type' => 'box',
                                    'layout' => 'horizontal',
                                    'margin' => 'md',
                                    'contents' => [
                                        [
                                            'type' => 'text',
                                            'text' => '1盎司',
                                            'flex' => 1,
                                        ],
                                        [
                                            'type' => 'text',
                                            'text' => $message['ounce']['buy'],
                                            'align' => 'center',
                                            'flex' => 2,
                                        ],
                                        [
                                            'type' => 'text',
                                            'text' => $message['ounce']['sell'],
                                            'align' => 'center',
                                            'flex' => 2,
                                        ],
                                    ],
                                ],
                                [
                                    'type' => 'box',
                                    'layout' => 'horizontal',
                                    'margin' => 'md',
                                    'backgroundColor' => '#7fb0ec',
                                    'contents' => [
                                        [
                                            'type' => 'text',
                                            'text' => '規格',
                                            'color' => '#FFFFFF',
                                            'flex' => 1,
                                        ],
                                        [
                                            'type' => 'text',
                                            'text' => '買進 (台幣)',
                                            'color' => '#FFFFFF',
                                            'flex' => 2,
                                        ],
                                        [
                                            'type' => 'text',
                                            'text' => '賣出 (台幣)',
                                            'color' => '#FFFFFF',
                                            'flex' => 2,
                                        ],
                                    ],
                                ],
                                [
                                    'type' => 'box',
                                    'layout' => 'horizontal',
                                    'margin' => 'md',
                                    'contents' => [
                                        [
                                            'type' => 'text',
                                            'text' => '1公克',
                                            'flex' => 1,
                                        ],
                                        [
                                            'type' => 'text',
                                            'text' => $message['gram']['buy'],
                                            'align' => 'center',
                                            'flex' => 2,
                                        ],
                                        [
                                            'type' => 'text',
                                            'text' => $message['gram']['sell'],
                                            'align' => 'center',
                                            'flex' => 2,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'footer' => [
                            'type' => 'box',
                            'layout' => 'vertical',
                            'contents' => [
                                [
                                    'type' => 'text',
                                    'text' => '玉山銀行 黃金價格 '. Carbon::now()->toDateString(),
                                    'align' => 'center',
                                    'gravity' => 'center',
                                ],
                            ],
                        ],
                    ],
                ],
            ] ;
        } // End getGoldFlexMessage()

        /**
         * 製作 Movie carousel
         *
         * @param Array Movies ['name' => '電影名稱', 'photo' => '電影圖片link', 'intro' => '電影介紹link', 'time' => '09:00, 10:30']
         * @return Array Template Message: carousel
         */
        public function getMovieCarousel($message)
        {
            // 輪播最大值為 10 筆
            $max = count($message) > 10 ? 10 : count($message) ;
            $carousel = [] ;

            for ($i = 0 ; $i < $max ; $i++) {
                $elem = [
                    'thumbnailImageUrl' => $message[$i]['photo'],
                    'imageBackgroundColor' => '#FFFFFF',
                    'title' =>  $message[$i]['name'],
                    'text' =>  $message[$i]['time'],
                    'defaultAction' => [
                        'type' => 'uri',
                        'label' => 'View Detail',
                        'uri' => 'https://www.google.com',
                    ],
                    'actions' => [
                        [
                            'type' => 'uri',
                            'label' => '查看電影細節',
                            'uri' => $message[$i]['intro'],
                        ]
                    ],
                ] ;
                array_push($carousel, $elem) ;
            }

            return $flex_messages = [
                [
                    'type' => 'template',
                    'altText' => 'This is a carousel template',
                    'template' => [
                        'type' => 'carousel',
                        'columns' => $carousel,
                    ],
                    'imageAspectRatio' => 'rectangle',
                    'imageSize' => 'cover',
                ],
            ] ;
        } // End getMovieCarousel()

        /**
         * 製作 Confirm Template
         *
         * @param String message: 
         * @return Array Flex Message
         */
        public function getConfirmTemplate()
        {
            
            return $flex_messages = [
                [
                    'type' => 'template',
                    'altText' => 'This is a confirm template',
                    'template' => [
                        'type' => 'confirm',
                        'text' => '您確定嗎?',
                        'actions' => [
                            [
                                'type' => 'message',
                                'label' => 'Yes',
                                'text' => 'yes',
                            ],
                            [
                                'type' => 'message',
                                'label' => 'No',
                                'text' => 'no',
                            ],
                        ],
                    ],
                ],
            ] ;
        } // End - getConfirmTemplate()

        /**
         * 製作 quickly reply: Location
         *
         * @param String message 
         * @return Array Flex Message
         */
        public function getLocation()
        {
            
            return $flex_messages = [
                [
                    'type' => 'text',
                    'text' => '傳送您的位置',
                    'quickReply' => [
                        'items' => [
                            [
                                'type' => 'action',
                                'action' => [
                                    'type' => 'location',
                                    'label' => 'Send Location',
                                ],
                            ],
                        ],
                    ],
                ],
            ] ;
        } // End - getLocation()
	}