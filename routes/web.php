<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('test')->group(function (){
	Route::get('/', 'TestController@index');
	// Route::match(['get'], 'show/{name}', 'TestController@show')->name('show');
	// Route::post('add', 'TestController@add')->name('add');
	// Route::post('delete/{id}', 'TestController@destroy');
	// Route::get('testdb', 'TestController@testdb');
});

////////////////////////////////////////////////////////////////

// Handle Languege config
Route::post('language', 'LoginController@language');

// user 帳號 進行登入行為
Route::post('/dologin', 'LoginController@auth')->name('dologin');

/*
GET		/member  				 index()	member.index
GET		/member/create 			 create()	member.create
POST	/member 				 store()	member.store
GET		/member/{member_id}		 show()		member.show
GET		/member/{member_id}/edit edit()		member.edit
PUT		/member/{member_id}	 	 update()	member.update
DELETE 	/member/{member_id}		 destroy()	member.destroy
*/
Route::group(['middleware' => ['language']], function () {
	// Dashboard
	Route::get('dashboard', 'DashboardController@index')->name('dashboard') ;

	// Seeder: member=MemberTableSeeder, group=GroupsTableSeeder
	Route::post('seeder', 'SeederController@index')->name('seeder');

	// 使用者
	Route::get('user', 'UserController@index')->name('user.index');
	Route::post('user/{id}', 'UserController@destroy')->name('user.destroy');
	Route::post('user/{id}/setApiToken', 'UserController@setApiToken')->name('user.setApiToken');
	Route::get('user/getApiToken', 'UserController@getApiToken')->name('user.getApiToken');

	// 會員
	Route::get('member/log', 'MemberController@log');
	Route::resource('member', 'MemberController');
	
	// 開放資料
	Route::prefix('opendata')->group(function (){
		Route::get('weather/now', 'WeatherController@index');
		Route::get('weather/now/api', 'WeatherController@getWeatherNow')->name('weather.now.api');
	});

	// API
	Route::get('api/getAllMember', 'ApiController@getAllMember');

	// Line Notify API
	Route::get('line/index', 'LineController@index')->name('line.index');
	Route::post('line/getCode', 'LineController@getCode'); // 取得 code
	Route::post('line/checkAccessToken/{user_id}', 'LineController@checkAccessToken')->name('line.checkAccessToken');
	Route::post('line/revokeAccessToken/{user_id}', 'LineController@revokeAccessToken')->name('line.revokeAccessToken');
	Route::post('line/send', 'LineController@sendNotify')->name('line.send'); // 送出訊息至 Line

	// Line Chat Bot: reply webhook callback url
	Route::post('line/reply', 'LinebotController@reply')->name('line.reply');
	Route::get('line/createRichmenu', 'LinebotController@createRichmenu');
	Route::get('line/listRichmenu', 'LinebotController@listRichmenu');
	Route::get('line/deleteRichmenu', 'LinebotController@deleteRichmenu');
	Route::get('line/uploadRichmenu', 'LinebotController@uploadRichmenu');
	Route::get('line/setDefaultRichmenu', 'LinebotController@setDefaultRichmenu');

	// SMTP Send Mail Test
	Route::get('mail/index', 'MailController@index')->name('mail.index');
	Route::post('mail/send', 'MailController@send')->name('mail.send');

	// Report
	Route::get('report/csv', 'ReportController@csv')->name('report.csv');
	Route::get('report/csv/download', 'ReportController@downloadCSV')->name('report.downloadCSV');
	Route::get('report/pdf', 'ReportController@pdf')->name('report.pdf');
	Route::get('report/pdf/download', 'ReportController@downloadPDF')->name('report.downloadPDF');
});

Auth::routes();
Route::get('/home', 'HomeController@index')->name('home');
