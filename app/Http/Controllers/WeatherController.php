<?php
	namespace App\Http\Controllers;

	use App\Http\Controllers\Controller;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Facades\App;
	use Illuminate\Support\Facades\Log;
	use App\Repositories\Henwen\ArrayHandleRepository;

	class WeatherController extends Controller
	{
		// 中央氣象局開放資料授權碼
		private $authorization ;

		// 36小時天氣預報 API
		private $api_uri ;

		// 陣列處理 Repository
		private $array_handler ;

		public function __construct(ArrayHandleRepository $array_handler)
		{
			$this->authorization = config('henwen.opendata.weather.authorization') ;
			$this->api_uri = config('henwen.opendata.weather.api_36_hours') ;
			$this->array_handler = $array_handler ;
		}

		public function index()
		{
			return view('weather.now', []) ;
		}

		public function getWeatherNow()
		{
			$data = array("status" => 0, "msg" => "") ;

			$field = array(
				"Authorization" => $this->authorization
			) ;

			try {
				$curlobj = curl_init() ;
				curl_setopt($curlobj, CURLOPT_HEADER, true) ;
				curl_setopt($curlobj, CURLOPT_NOBODY, false) ;
				curl_setopt($curlobj, CURLOPT_RETURNTRANSFER, true) ;
				curl_setopt($curlobj, CURLOPT_TIMEOUT, 10) ;
				curl_setopt($curlobj, CURLOPT_URL, $this->api_uri."?".http_build_query($field)) ;
				$output = curl_exec($curlobj) ;
				$httpcode = curl_getinfo($curlobj, CURLINFO_HTTP_CODE) ;
				
				if ($httpcode !== 200) {
					throw new \Exception("取得天氣資料失敗", 1) ;
				}
				else {
					// 分離 Header 和 Body
					list($header, $body) = explode("\r\n\r\n", $output, 2) ;
				}

				if ($body) {
					$weather = json_decode($body, true) ;
					$city_temp_data = array() ;

					for($i = 0 ; $i < count($weather["records"]["location"]) ; $i++) {
						$city = array(
							"city_name" => $weather["records"]["location"][$i]["locationName"],
							"min_t" => 0,
							"max_t" => 0,
						) ;

						foreach ($weather["records"]["location"][$i]["weatherElement"] as $obj) {
							// 儲存時段
							if ($i === 0 && $obj["elementName"] === "MinT") {
								$start_time = $obj["time"][0]["startTime"] ; // 2020-07-08 12:00:00
								$end_time = $obj["time"][0]["endTime"] ; // 2020-07-08 18:00:00
							}

							// 最低溫度
							if ($obj["elementName"] === "MinT") {
								$city["min_t"] = $obj["time"][0]["parameter"]["parameterName"] ;
							}

							// 最高溫度
							if ($obj["elementName"] === "MaxT") {
								$city["max_t"] = $obj["time"][0]["parameter"]["parameterName"] ;
							}
						}
						array_push($city_temp_data, $city) ;
					}

					$data = array(
						"status" => 1,
						"x" => array_column($city_temp_data, "city_name"),
						"y1" => $this->array_handler->getArrayIntFromKey($city_temp_data, "min_t"),
						"y2" => $this->array_handler->getArrayIntFromKey($city_temp_data, "max_t"),
						"st" => $start_time,
						"et" => $end_time,
						"msg" => "Success"
					) ;
				}
			} catch (\Exception $e) {
				$data["msg"] = $e->getMessage() ;
				Log::error($e->getMessage()) ;
			} finally {
				// Return Array, will convert to JSON
				return $data ;
			}
		}
	}