<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('language');
        $this->expired_days = config('henwen.token.expired_days', 7) ;
    }
    
    public function index()
    {
        $perpage = config('henwen.paginate.user.list', 10) ;
        $users = User::getUserPaginate($perpage) ;
        return view('user.index', ['users' => $users, 'now' => Carbon::now()]) ;
    }

    /**
     * 設定 user 的 API Token
     *
     * @return \Illuminate\Http\Response
     */
    public function setApiToken(Request $request, $id)
    {
    	$action = $request->input('action') ;
    	if ($action === 'set') {
    		$user = User::find($id) ;
	        $user->api_token = hash('sha256', time().$user->name.$user->email) ;
	        $user->api_token_expired = Carbon::now()->addDays($this->expired_days)->toDateTimeString() ;
    	}
    	else if ($action === 'clean') {
    		$user = User::find($id) ;
    		$user->api_token = null ;
    		$user->api_token_expired = null ;
    	}
    	$user->save() ;

        return redirect()->back() ;
    }

    /**
     * 透過登入, 取得 user 的 API Token
     *
     * @return \Illuminate\Http\Response
     */
    public function getApiToken(Request $request)
    {
    	$name = $request->input('name') ;
    	$password = $request->input('password') ;

    	if (Auth::attempt(['name' => $name, 'password' => $password])) {
    		$user = User::find(Auth::user()->id) ;

    		if ($user->status !== 1) {
    			return $data = [
	    			'status' => 0,
	    			'message' => 'User is invalid',
	    			'api_token' => '',
	    			'api_token_expired' => '',
	    		] ;
    		}

    		// 尚未產生 token 或 token 已過期, 則產生一組 Token
    		if (empty($user->api_token) || $user->api_token_expired < Carbon::now()) {
    			$user->api_token = hash('sha256', time().$user->name.$user->email) ;
    			$user->api_token_expired = Carbon::now()->addDays($this->expired_days)->toDateTimeString() ;
    			$user->save() ;
    		}

    		return $data = [
    			'status' => 1,
    			'message' => 'Login Success',
    			'api_token' => $user->api_token,
    			'api_token_expired' => $user->api_token_expired,
    		] ;
    	}
    	else {
    		return $data = [
    			'status' => 0,
    			'message' => 'Login Fail',
    			'api_token' => '',
    			'api_token_expired' => '',
    		] ;
    	}
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    	$user = User::find($id) ;
        if (! empty($user->first())) {
        	$user->deleted_at = Carbon::now() ;
        	$user->status = 2 ;
        	$user->save() ;
            return redirect()->back() ;
        }
        else {
            echo "Error" ;
        }
    }
}
