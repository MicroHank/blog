<?php
	namespace App\Http\Controllers;

	use App\Http\Controllers\Controller;
	use Illuminate\Support\Facades\App;
	use Illuminate\Support\Facades\Config;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Http\Request;

	class LoginController extends Controller
	{
	    /**
	     * Show a list of all of the application's users.
	     *
	     * @return Response
	     */
	    public function index()
	    {
	        
	    }

	    public function language(Request $request)
	    {
	    	// 設定語系資料
	        if ($request->has("language")) {
	            $language = strtolower($request->input("language")) ;
	        }
	        else {
	            $language = Config::get("app.fallback_locale") ;
	        }

	        // 重新導向並帶著 Cookie
	        return redirect()->back()->withCookie(cookie()->forever("language", $language)) ;
	    }

	    public function auth(Request $request)
	    {
	    	$name = $request->input('name') ;
	    	$password = $request->input('password') ;

	    	// 進行登入動作
	    	if (Auth::attempt(['name' => $name, 'password' => $password])) {
	    		return redirect()->route('dashboard') ;
	    	}
	    	else {
	    		return redirect()->back()->withInput() ;
	    	}
	    }
	}