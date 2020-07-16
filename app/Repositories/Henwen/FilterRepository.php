<?php

	namespace App\Repositories\Henwen;

	class FilterRepository
	{
		public function __construct() {}

		/**
		 * getBoolean():
		 *
		 * 判斷是否為 Boolean, 是的話回傳 true 或 false, 不是的話回傳 NULL
		 * @param: Boolean $bool, e.g. "true", "1" => true ; "false", "0", NULL => false, "3", "null" => NULL
		 * @return Boolean | NULL
		 */
		public function getBoolean($bool = true)
		{
			return filter_var($bool, FILTER_VALIDATE_BOOLEAN, array("flags" => FILTER_NULL_ON_FAILURE)) ;
		}

		/**
		 * getFloat():
		 *
		 * 判斷是否為有效的浮點數
		 * @param: Float $float, e.g. 123.1, "1,100.5" (千位逗號)
		 * @return Float
		 */
		public function getFloat($float = 1.0)
		{
			$$float  = filter_var($float, FILTER_SANITIZE_NUMBER_FLOAT) ;
			$options = array("default" => 0.0) ;
			$flags   = FILTER_FLAG_ALLOW_THOUSAND ;
			return filter_var($float, FILTER_VALIDATE_FLOAT, array("options" => $options, "flags" => $flags)) ;
		}

		/**
		 * getInt():
		 *
		 * 將傳入的變數做 INT 的消毒, 再回傳其 INT 數值, 如果非數值則回傳 0
		 * @param: INT $number, "1a2" => 12, "aaa" => 0
		 * @return INT
		 */
		public function getInt($number = 0)
		{
			// 去除數值以外的字元
			$number  = filter_var($number, FILTER_SANITIZE_NUMBER_INT) ;
			$options = array("default" => 0) ;
			$flags   = FILTER_FLAG_ALLOW_OCTAL ;
			return filter_var($number, FILTER_VALIDATE_INT, array("options" => $options, "flags" => $flags)) ;
		}

		/**
		 * getIntWithRange():
		 *
		 * 將傳入的變數做 INT 的消毒, 並且限定在 [min, max] 範圍內, 否則回傳 fail_return_value
		 * @param: INT $number, 原變數
		 * @param: INT $min, 範圍最小值
		 * @param: INT $max, 範圍最大值
		 * @param: INT $fail_return_value, 超過範圍時的回傳值
		 * @return INT
		 */
		public function getIntWithRange($number = 0, $min = 1, $max = 10, $fail_return_value = 1)
		{
			// 去除數值以外的字元
			$number = $this->getInt($number) ;
			$min	= $this->getInt($min) ;
			$max	= $this->getInt($max) ;
			$fail_return_value = $this->getInt($fail_return_value) ;

			$options = array("default" => $fail_return_value, "min_range" => $min, "max_range" => $max) ;
			return filter_var($number, FILTER_VALIDATE_INT, array("options" => $options)) ;
		}

		/**
		 * getEmail():
		 *
		 * 判斷是否為有效的 Email 字串, 是的話回傳其原字串, 不是的話回傳空字串 ""
		 * @param: String $email, e.g. "123@gmail.com"
		 * @return String
		 */
		public function getEmail($email = "Service@gmail.com")
		{
			$email = filter_var($email, FILTER_SANITIZE_EMAIL) ;
			$email = filter_var($email, FILTER_SANITIZE_STRING) ;
			$options = array("default" => "") ;
			return filter_var($email, FILTER_VALIDATE_EMAIL, array("options" => $options)) ;
		}

		/**
		 * getUrl():
		 *
		 * 判斷是否為有效的 URL 字串, 是的話回傳其原字串, 不是的話回傳空字串 ""
		 * @param: String $url, e.g. "http://www.google.com.tw"
		 * @return String
		 */
		public function getUrl($url = "http://www.google.com.tw")
		{
			$url = filter_var($url, FILTER_SANITIZE_URL) ;
			$url = filter_var($url, FILTER_SANITIZE_STRING) ;
			$options = array("default" => "") ;
			return filter_var($url, FILTER_VALIDATE_URL, array("options" => $options)) ;
		}

		/**
		 * getString():
		 *
		 * 消毒 String,  可以正常顯示 ' 與 ", 不會把單引號 ' encode 成 「&#039;」 雙引號 " encode 成 「&quot;」
		 * @param: String $string, e.g. "www;'< test >"
		 * @return String
		 */
		public function getString($string = "")
		{
			// htmlspecialchars(string) 加入 FILTER_FLAG_NO_ENCODE_QUOTES 
			return filter_var($string, FILTER_SANITIZE_FULL_SPECIAL_CHARS, array("flags" => FILTER_FLAG_NO_ENCODE_QUOTES)) ;
		}
	}