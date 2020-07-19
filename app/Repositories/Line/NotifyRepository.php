<?php
	namespace App\Repositories\Line;

	use Illuminate\Support\Facades\DB;

	class NotifyRepository
	{
		public function __construct() {}

		/**
		 * 執行 oAuth 流程
		 * curl Method = POST
		 * URL: https://notify-bot.line.me/oauth/token
		 *
		 * @return Array ['status' => 200, 'access_toke' => 'xxxxx']
		 */
		public static function getUserAccessToken($code)
		{
			// 取得 Access Token
	        $url = "https://notify-bot.line.me/oauth/token" ;
	        $field = [
	            'grant_type' => 'authorization_code',
	            'redirect_uri' => 'http://127.0.0.1/laravel/blog/public/line/getCode',
	            'client_id' => config('henwen.line.client_id'),
	            'client_secret' => config('henwen.line.client_secret'),
	            'code' => $code,
	        ] ;
	        $curlobj = curl_init() ;
	        curl_setopt($curlobj, CURLOPT_RETURNTRANSFER, true) ;
	        curl_setopt($curlobj, CURLOPT_TIMEOUT, 10) ;
	        curl_setopt($curlobj, CURLOPT_POST, true);
	        curl_setopt($curlobj, CURLOPT_URL, $url."?".http_build_query($field)) ;
	        $output = curl_exec($curlobj) ;
	        return $result = json_decode($output, true) ;
		}

		/**
	     * 取得 Access Token 的狀態
	     * curl Method = GET
	     * @param string $access_token, e.g. xjSLYAbA2tegHNR1MRTwcBKKhCP7UNlN6il89OarDNg
	     * 
	     * @return Array ['status' => 200, 'message' => 'ok', 'targetType' => 'User', 'target' => 'My Line Account']
	     */
	    public function getAccessTokenStatus($access_token)
	    {
	        $url = 'https://notify-api.line.me/api/status' ;

	        $curlobj = curl_init() ;
	        curl_setopt($curlobj, CURLOPT_HTTPHEADER, array(
	            'Authorization: Bearer '.$access_token,
	        ));
	        curl_setopt($curlobj, CURLOPT_RETURNTRANSFER, true) ;
	        curl_setopt($curlobj, CURLOPT_TIMEOUT, 10) ;
	        curl_setopt($curlobj, CURLOPT_POST, false) ;
	        curl_setopt($curlobj, CURLOPT_URL, $url) ;
	        $output = curl_exec($curlobj) ;
	        return json_decode($output, true) ;
	    }
	    
	    /**
	     * 註銷 Access Token
	     * curl Method = POST
	     * @param string $access_token, e.g. xjSLYAbA2tegHNR1MRTwcBKKhCP7UNlN6il89OarDNg
	     * 
	     * @return Array ['status' => 200, 'message' => 'ok'], ['status' => 401, 'message' => 'Invalid access token']
	     */
	    public function revokeAccessToken($access_token)
	    {
	        $url = 'https://notify-api.line.me/api/revoke' ;

	        $curlobj = curl_init() ;
	        curl_setopt($curlobj, CURLOPT_HTTPHEADER, array(
	            'Authorization: Bearer '.$access_token,
	        ));
	        curl_setopt($curlobj, CURLOPT_RETURNTRANSFER, true) ;
	        curl_setopt($curlobj, CURLOPT_TIMEOUT, 10) ;
	        curl_setopt($curlobj, CURLOPT_POST, true) ;
	        curl_setopt($curlobj, CURLOPT_URL, $url) ;
	        $output = curl_exec($curlobj) ;
	        return json_decode($output, true) ;
	    }

	    /**
	     * 寄送通知
	     * curl Method = POST
	     * @param string $access_token, e.g. xjSLYAbA2tegHNR1MRTwcBKKhCP7UNlN6il89OarDNg
	     * @param string $message, e.g. Hello World
	     * 
	     * @return Array ['status' => 200, 'message' => 'ok']
	     */
		public function sendNotifyToUser($access_token, $message)
		{
			$url = 'https://notify-api.line.me/api/notify' ;
	        $field = [
	            'message' => $message,
	            'stickerPackageId' => '1',
	            'stickerId' => '119',
	        ] ;
	        $curlobj = curl_init() ;
	        curl_setopt($curlobj, CURLOPT_HTTPHEADER, array(
	            'Authorization: Bearer '.$access_token,
	        ));
	        curl_setopt($curlobj, CURLOPT_RETURNTRANSFER, true) ;
	        curl_setopt($curlobj, CURLOPT_TIMEOUT, 10) ;
	        curl_setopt($curlobj, CURLOPT_POST, true);
	        curl_setopt($curlobj, CURLOPT_URL, $url."?".http_build_query($field)) ;
	        $output = curl_exec($curlobj) ;
	        return json_decode($output, true) ;
		}
	}