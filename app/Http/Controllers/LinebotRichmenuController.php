<?php

namespace App\Http\Controllers;

class LinebotRichmenuController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {}

    // 查詢已建立的 Rich Menu ID
    public function index()
    {
        $ch = curl_init() ;
        curl_setopt($ch, CURLOPT_URL, 'https://api.line.me/v2/bot/richmenu/list') ;
        curl_setopt($ch, CURLOPT_POST, false) ;
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ;
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' .config('line.channel_access_token')
        ]) ;
        $result = curl_exec($ch) ;
        curl_close($ch) ;
        var_dump($result) ;
    }

    // 建立 Rich Menu 並取得一個 Rich Menu ID
    public function create()
    {
        $data = [
            'size' => [
                'width' => 800,
                'height' => 540,
            ],
            'selected' => false,
            'name' => 'Henwen Richmenu',
            'chatBarText' => '打開指令',
            'areas' => [
                [
                    'bounds' => [
                        'x' => 0,
                        'y' => 0,
                        'width' => 266,
                        'height' => 270,
                    ],
                    'action' => [
                        'type' => 'message',
                        'text' => 'user',
                    ],
                ],
                [
                    'bounds' => [
                        'x' => 267,
                        'y' => 0,
                        'width' => 266,
                        'height' => 270,
                    ],
                    'action' => [
                        'type' => 'message',
                        'text' => 'member',
                    ],
                ],
                [
                    'bounds' => [
                        'x' => 533,
                        'y' => 0,
                        'width' => 266,
                        'height' => 270,
                    ],
                    'action' => [
                        'type' => 'message',
                        'text' => 'mail',
                    ],
                ],
                [
                    'bounds' => [
                        'x' => 0,
                        'y' => 270,
                        'width' => 266,
                        'height' => 270,
                    ],
                    'action' => [
                        'type' => 'message',
                        'text' => 'server',
                    ],
                ],
                [
                    'bounds' => [
                        'x' => 267,
                        'y' => 270,
                        'width' => 266,
                        'height' => 270,
                    ],
                    'action' => [
                        'type' => 'message',
                        'text' => 'sql',
                    ],
                ],
                [
                    'bounds' => [
                        'x' => 533,
                        'y' => 270,
                        'width' => 266,
                        'height' => 270,
                    ],
                    'action' => [
                        'type' => 'uri',
                        'uri' => config('henwen.github_uri'),
                    ],
                ],
            ],
        ] ;

        $ch = curl_init() ;
        curl_setopt($ch, CURLOPT_URL, 'https://api.line.me/v2/bot/richmenu') ;
        curl_setopt($ch, CURLOPT_POST, true) ;
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ;
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)) ;
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' .config('line.channel_access_token')
        ]) ;
        $result = curl_exec($ch) ;
        curl_close($ch) ;
        var_dump($result) ;
    }

    // 刪除 Rich Menu ID
    public function delete($rich_id)
    {
        $ch = curl_init() ;
        curl_setopt($ch, CURLOPT_URL, 'https://api.line.me/v2/bot/richmenu/'.$rich_id) ;
        curl_setopt($ch, CURLOPT_POST, false) ;
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE') ;
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ;
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' .config('line.channel_access_token')
        ]) ;
        $result = curl_exec($ch) ;
        curl_close($ch) ;
        var_dump($result) ;
    }

    // 上傳圖片至指定的 Rich Menu ID
    public function upload()
    {
        $image = base_path('public/images/richmenu2.jpg') ;

        $data = [
            'name' => 'richmenu.jpg',
            'file' => $image,
        ] ;
        $ch = curl_init() ;
        curl_setopt($ch, CURLOPT_URL, 'https://api.line.me/v2/bot/richmenu/'.config('line.rich_id').'/content') ;
        curl_setopt($ch, CURLOPT_POST, true) ;
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data) ;
        curl_setopt($ch,CURLOPT_POSTFIELDS, file_get_contents($image)) ;
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ;
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' .config('line.channel_access_token'),
            'Content-Type: image/jpeg',
            'Content-Length:'. filesize($image),
        ]) ;
        $result = curl_exec($ch) ;
        curl_close($ch) ;
        var_dump($result) ;
    }

    // 設定所有使用者, 使用指定 Rich Menu ID 的圖片
    public function setDefault()
    {
        $ch = curl_init() ;
        curl_setopt($ch, CURLOPT_URL, 'https://api.line.me/v2/bot/user/all/richmenu/'.config('line.rich_id')) ;
        curl_setopt($ch, CURLOPT_POST, true) ;
        curl_setopt($ch,CURLOPT_POSTFIELDS, "") ;
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' .config('line.channel_access_token'),
        ]) ;
        $result = curl_exec($ch) ;
        curl_close($ch) ;
        var_dump($result) ;
    }
}
