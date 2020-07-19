<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Line;
use App\Repositories\Line\NotifyRepository;

class LineController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {}

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
    public function getCode(Request $request, NotifyRepository $notify_rep)
    {
        if ($request->has('code')) {
            // 訂閱一對一或群組連動後, callback POST 回此 URI 回傳 code 參數
            $code = $request->input('code', '') ;

            // 透過 oAuth 取得 Access Token
            $result = $notify_rep->getUserAccessToken($code) ;

            // 取得 Access Token status = 200
            if ($result['status'] == 200) {

                // 取得 Access Token 的狀態與連動目標
                $status = $notify_rep->getAccessTokenStatus($result['access_token']) ;

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
     * 將訊息送至指定使用者訂閱的 Line 通知框 (單一或群組)
     */
    public function sendNotify(Request $request, NotifyRepository $notify_rep)
    {
        $user_id = (int) $request->input('user_id') ;
        $message = $request->input('message') ;

        // Get Username and Access Token
        $user = DB::table('line AS l')->leftJoin('users AS u', 'l.user_id', '=', 'u.id')
        ->select('u.name', 'l.access_token')->where('u.id', '=', $user_id)->first() ;

        $user_name = $user->name ;
        $access_token = $user->access_token ;
        $message = 'Hello '. $user_name.': '.$message ;

        // Call Notify API: Send Message
        $result = $notify_rep->sendNotifyToUser($access_token, $message) ;

        return redirect()->back()->with('message', $result['message']) ;
    }

    /**
     * 檢驗 Access Token 的狀態
     *
     * return redirect
     */
    public function checkAccessToken(NotifyRepository $notify_rep)
    {
        $line = Line::where('user_id', Auth::user()->id) ;
        
        if (count($line->first()) > 0) {
            $data = $line->first() ;
            $access_token = $data['access_token'] ;
        }
        else {
            return redirect()->back()->with('message', '尚未訂閱 Line Notify 連動') ;
        }
        
        $result = $notify_rep->getAccessTokenStatus($access_token) ;

        $message = $result['message'].': 你的訂閱為 ('.$result['targetType'].') '.$result['target'] ;

        return redirect()->back()->with('message', $message) ;
    }
}
