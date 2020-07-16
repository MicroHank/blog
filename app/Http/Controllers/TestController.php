<?php
	namespace App\Http\Controllers;

	use App\Http\Controllers\Controller;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Facades\App;
	use Illuminate\Support\Facades\Log;
	use Illuminate\Http\Request;
	use Carbon\Carbon;
	use App\Models\User;
	use App\Models\Group;
	use App\Models\UserGroup;
	use App\Models\Member;

	// Jobs
	use App\Jobs\TestJob;

	// Event
	use Event;
	use App\Events\OrderMessageEvent;
	use Illuminate\Support\Facades\Artisan;

	class TestController extends Controller
	{
		public function __construct()
		{
			DB::enableQueryLog();
		}
	    /**
	     * Show a list of all of the application's users.
	     *
	     * @return Response
	     */
	    public function index()
	    {
	    	var_dump(Member::all()->pluck('user_id')->toArray()) ;
	    	// try {
	    	// 	$output = Artisan::call('db:seed', ['--class' => 'GroupsTableSeeder']) ;
	    	// 	var_dump($output) ;
	    	// } catch (\Exception $e) {
	    	// 	echo $e->getMessage() ;
	    	// }
	    	
	    	// $user_group = User::find(1)->groupid ;
	    	// foreach ($user_group as $pair) {
	    	// 	var_dump($pair->user_id) ;
	    	// 	var_dump($pair->group_id) ;
	    	// }

	    	// $user_group = Member::groups() ;
	    	// foreach ($user_group as $user) {
	    	// 	var_dump($user->user_id) ;
	    	// 	var_dump($user->account) ;
	    	// 	var_dump($user->user_name) ;
	    	// 	var_dump($user->group_name) ;
	    	// }

	    	// $member = new Member ;
	    	// $member->account = rand() ;
	    	// $member->password = password_hash('123', PASSWORD_DEFAULT) ;
	    	// $member->user_name = 'Henwen'.rand() ;
	    	// $member->supervisor_id = 0 ;
	    	// $member->save() ;

	        // $users = Member::where('user_id', '<=', 10)->take(5)->get() ;
	        // foreach ($users as $user) {
	        // 	var_dump($user->user_name) ;
	        // }

	        // try {
		    //     $user = Member::findOrFail(111) ; // findOrFail throws Exception
		    //     var_dump($user->user_id) ;
		    //     var_dump($user->user_name) ;
		    // } catch (\Exception $e) {
		    // 	echo $e->getMessage() ;
		    // }

	        // $users = Member::find([2, 3, 4]) ;
	        // foreach ($users as $user) {
	        // 	var_dump($user->user_id) ;
	        // 	var_dump($user->user_name) ;
	        // }

	        // $user = Member::where('user_id', 11)->firstOrFail() ;
	        // var_dump($user->user_id) ;
	        // var_dump($user->user_name) ;

	        // $count = Member::where('user_id', '<=', 15)->count() ;
	        // var_dump($count) ;

	        // $group = new Group ;
	        // $group->group_name = "NewGroupByORM-".rand() ;
	        // $group->save() ;

	        // $groups = Group::all() ;
	        // foreach ($groups as $group) {
	        // 	var_dump($group->group_name) ;
	        // }

	        // Update
	        // Member::where('user_name', 'Henwen')->update(['user_name' => 'HenwenNew']) ;

	    	// var_dump(App::basePath()) ;
	    	// var_dump(App::environment()) ;
	    	// App::setLocale('es') ;
	    	// var_dump(App::getLocale()) ;
	    	// var_dump(config('app.timezone')) ;
	    	// var_dump(config('database.default')) ;
	    	// var_dump(config('henwen.name')) ;
	        
	        // Jobs
	        //dispatch(new TestJob());

	        // Event
	        //Event::fire(new OrderMessageEvent($users));
	    }

	    public function show($name)
	    {
	    	return view('test.index', ['name' => $name]) ;
	    }

	    public function testdb()
	    {
	    	try {
	    		// $id = 11 ;
	    		// Collection of Object(stdClass)
	    		// $user = DB::table('user')->where('user_id', '=', $id)->get() ;
	    		// dd(DB::getQueryLog()) ;
	    		// Log::info() ;

	    		// if (! $user->isEmpty()) {
	    		// 	var_dump($user[0]->user_name) ;
	    		// }
	    		// else {
	    		// 	Log::info('Query user_id = '. $id . " does not exist.") ;
	    		// }

	    		// Object(stdClass)
		    	// $user = DB::table('user')->where('user_id', '=', $id)->first() ;
		    	// var_dump($user) ;
		    	// if (! empty($user)) {
		    	// 	var_dump($user->user_name) ;
		    	// }
		    	// else {
		    	// 	Log::info('Query user_id = '. $id . " does not exist.") ;
		    	// }

		    	// Single column
		    	// $data = DB::table('user')->where('user_id', '=', $id)->value('user_name') ;
		    	// echo $data ;

		    	// An Array in a Collection
		    	// $users = DB::table('user')->pluck('user_name') ;
		    	// foreach ($users as $user_name) {
				//     echo $user_name ;
				// }
		    	
		    	// DB::table('user')->orderBy('user_id')->chunk(5, function($users){
		    	// 	foreach ($users as $user) {
		    	// 		var_dump($user->user_name) ;
		    	// 	}
		    	// }) ;

		    	// Where 條件, 欄位帶函數
		    	// $users = DB::table('user')->where([
		    	// 	['user_id', '>=', 1],
		    	// 	[DB::raw('length(user_name)'), '<=', 10],
		    	// ])->get() ;
		    	// foreach ($users as $user) {
		    	// 	var_dump($user->user_name) ;
		    	// }

		    	// $user_id = DB::table('user')->insertGetId([
		    	// 	'account' => rand(),
		    	// 	'password' => password_hash('123', PASSWORD_BCRYPT, ["cost" => 10]),
		    	// 	'user_name' => 'Henwen'.substr(rand(),1,4),
		    	// 	'supervisor_id' => 0,
		    	// ]) ;
		    	// var_dump($user_id) ;

		    	// $users = DB::table('user')->paginate(5) ;
		    	// var_dump($users) ;


	    	} catch (\Exception $e) {
	    		Log::error($e->getMessage()) ;
	    	}
	    	
	    }

	}