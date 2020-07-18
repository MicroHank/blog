<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Line;

class LineController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        
    }

    public function index()
    {
        // 已訂閱的使用者
        $line_notify = DB::table('line AS l')->leftJoin('users AS u', 'l.user_id', '=', 'u.id')
        ->select('l.*', 'u.name')->get() ;

        return view('line.index', ['line_notify' => $line_notify]) ;
    }

     /**
     * 透過 oAuth2 callback 取得 Code, 呼叫 API 取得 Access Token, 儲存至 line Table
     * 
     * @return redirect
     */
    public function getCode(Request $request)
    {
        if ($request->has('code')) {
            $code = $request->input('code', '') ;

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
            $result = json_decode($output, true) ;

            // 取得正常 status = 200
            if ($result['status'] == 200) {

                // 取得 Access Token 的狀態與連動目標
                $status = $this->getAccessTokenStatus($result['access_token']) ;

                if ($status['status'] == 200) {
                    // 檢驗登入的使用者 (user_id) 是否訂閱 Line Notify 連動使用者或群組
                    $line = Line::where('user_id', Auth::user()->id) ;

                    // 尚未訂閱: 新增訂閱資料
                    if (count($line->first()) === 0) {
                        $line = new Line ;
                        $line->user_id = Auth::user()->id ;
                        $line->access_token = $result['access_token'] ;
                        $line->target_type = $status['targetType'] ;
                        $line->target = $status['target'] ;
                        $line->save() ;
                    }
                    // 已訂閱過: 更新
                    else {
                        $line->update([
                            'access_token' => $result['access_token'],
                            'target_type' => $status['targetType'],
                            'target' => $status['target'],
                        ]) ;
                    }
                }
                return redirect()->route('line.index')->with('message', 'Get Access Token OK.') ;
            }
            else {
                return redirect()->route('line.index')->with('message', 'Get Access Token Fail.') ;
            }
        }
        else {
                return redirect()->route('line.index')->with('message', 'Get Code Fail.') ;
        }
    }

    /**
     * 取得 Access Token 的狀態
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
        curl_setopt($curlobj, CURLOPT_POST, false);
        curl_setopt($curlobj, CURLOPT_URL, $url) ;
        $output = curl_exec($curlobj) ;
        return json_decode($output, true) ;
    }

    /**
     * 將訊息送至指定使用者訂閱的 Line 通知框 (單一或群組)
     */
    public function sendNotify(Request $request)
    {
        $user_id = (int) $request->input('user_id') ;
        $message = $request->input('message') ;

        // Get Username and Access Token
        $user = DB::table('line AS l')->leftJoin('users AS u', 'l.user_id', '=', 'u.id')
        ->select('u.name', 'l.access_token')->where('u.id', '=', $user_id)->first() ;

        $user_name = $user->name ;
        $access_token = $user->access_token ;

        // Call Notify API: Send Message
        $url = 'https://notify-api.line.me/api/notify' ;
        $field = [
            'message' => 'Hello '. $user_name.': '.$message,
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
        $result = json_decode($output, true) ;

        return redirect()->back()->with('message', $result['message']) ;
    }

    /**
     * 檢驗 Access Token 的狀態
     *
     * return redirect
     */
    public function checkAccessToken()
    {
        $line = Line::where('user_id', Auth::user()->id) ;
        
        if (count($line->first()) > 0) {
            $data = $line->first() ;
            $access_token = $data['access_token'] ;
        }
        else {
            return redirect()->back()->with('message', '尚未訂閱 Line Notify 連動') ;
        }
        
        $result = $this->getAccessTokenStatus($access_token) ;
        $message = $result['message'].': 你的訂閱為 ('.$result['targetType'].') '.$result['target'] ;

        return redirect()->back()->with('message', $message) ;
    }
}
