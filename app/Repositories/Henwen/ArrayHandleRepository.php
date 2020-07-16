<?php
	namespace App\Repositories\Henwen;

	class ArrayHandleRepository
	{
		// 排序方法之常數
		private $sort_type = array(SORT_ASC, SORT_DESC) ;

		public function __construct() {}

		/**
		 * getArrayINT():
		 *
		 * 將陣列內容全部轉成整數
		 * @param: Array $array, 陣列內容
		 * @return Array INT
		 */
		public function getArrayInt($array = [])
		{
			if ( ! is_array($array)) throw new \Exception("Invalid Type : Array") ;
			return array_map("intval", $array) ;
		}

		/**
		 * getArrayINTFromKey():
		 *
		 * 取得陣列中的指定欄位, 並將其內容全部轉成整數
		 * @param: Array $array, 陣列內容
		 * @param: String $key_name, 欄位名稱, e.g. client_id
		 * @return Array INT
		 */
		public function getArrayIntFromKey($array = [], $key_name = "")
		{
			return $this->getArrayInt(array_column($array, $key_name)) ;
		}

		/**
		 * getArraySumFromKey():
		 *
		 * 回傳陣列指定欄位的加總
		 * @param: Array $array, 陣列內容
		 * @param: String $key_name, 欄位名稱, e.g. cost
		 * @return INT, 總和
		 */
		public function getArraySumFromKey($array = [], $key_name = "")
		{
			return array_sum(array_column($array, $key_name)) ;
		}

		/**
		 * sortArrayFromKey():
		 *
		 * 針對陣列中的指定欄位做排序: 可選 升冪與降冪 排序
		 * @param: Array & $array, 陣列的位址
		 * @param: String $key_name, 欄位名稱, e.g. client_id
		 * @param: INT $sort_type, 排序方式, e.g. SORT_ASC 升冪, SORT_DESC 降冪
		 * @return void, 直接改變其陣列內容
		 */
		public function sortArrayFromKey(& $array = [], $key_name = "", $sort_type = SORT_ASC)
		{
			if ( ! in_array($sort_type, $this->sort_type)) throw new \Exception("Invalid Sort Type: SORT_ASC, SORT_DESC") ;
			$sortBy = array() ;
	        foreach ($array as $key => $row) {
	            $sortBy[$key] = $row[$key_name] ;
	        }
	        array_multisort($sortBy, $sort_type, $array) ;
		}
	}