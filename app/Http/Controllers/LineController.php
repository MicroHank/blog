<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Validator;
use App\Models\Line;
use App\Models\User;
use App\Models\Member;
use App\Models\Linebot;
use App\Repositories\Line\NotifyRepository;
use App\Repositories\Mail\LineChatbotRepository;

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
    public function checkAccessToken(NotifyRepository $notify_rep, $user_id)
    {
        $line = Line::where('user_id', $user_id) ;
        
        if (count($line->first()) > 0) {
            $data = $line->first() ;
            $access_token = $data['access_token'] ;
        }
        else {
            return redirect()->back()->with('message', '尚未訂閱 Line Notify 連動') ;
        }
        
        $result = $notify_rep->getAccessTokenStatus($access_token) ;

        $message = $result['message'].': 此訂閱為 ('.$result['targetType'].') '.$result['target'] ;

        return redirect()->back()->with('message', $message) ;
    }

    /**
     * 註銷 Access Token
     *
     * return redirect
     */
    public function revokeAccessToken(NotifyRepository $notify_rep, $user_id)
    {
        $line = Line::where('user_id', $user_id) ;
        
        if (count($line->first()) > 0) {
            $data = $line->first() ;
            $access_token = $data['access_token'] ;
        }
        else {
            return redirect()->back()->with('message', '尚未訂閱 Line Notify 連動') ;
        }
        
        $result = $notify_rep->revokeAccessToken($access_token) ;

        // 註銷成功, 刪除 line Table 儲存的 User Access Token 資料
        if ($result['status'] == 200) {
            $line->delete() ;
        }

        $message = '註銷 Access Token 結束: '. $result['message'] ;

        return redirect()->back()->with('message', $message) ;
    }

    /**
     * Line Bot callback URL
     */
    public function reply()
    {
        /*
         * 處理 Callback
         * 從 Line callback 接收一串 JSON String
         */
        // Receive Json String
        $receive_string = file_get_contents('php://input') ;
        $obj = json_decode($receive_string, true) ;
        Log::info($receive_string) ;

        // Data from receive string
        $eventsType = $obj['events'][0]['type'] ; // Text: (message) User (follow, unfollow) Group (join, leave)
        $userId = isset($obj['events'][0]['source']['userId']) ? $obj['events'][0]['source']['userId'] : 0 ; // 發送訊息的使用者編號
        $groupId = isset($obj['events'][0]['source']['groupId']) ? $obj['events'][0]['source']['groupId'] : 0 ; // 在哪個群組發送
        $type = $obj['events'][0]['source']['type'] ; // 回傳值: user, group
        $replyToken = $obj['events'][0]['replyToken'] ; // 回應的 Token, 用於 reply api
        $text = strtolower($obj['events'][0]['message']['text']) ; // 使用者傳送的訊息
        Log::info($text) ;
        //---------------------------------------------------------//

        /*
         * 處理使用者 (userId) 目前的 phase string
         * 堆疊指令至 $phase 陣列
         * $phase = ['member', 'get', 'account'], ['user', 'delete', 'name'], ['mail', 'send', 'henwen.work@gmail.com']
         */
        $send_time = Carbon::now() ;
        $send_time_diff_sec = config('line.send_time_diff_sec') ;

        if ($user = Linebot::find($userId)) {
            // 上次送指令的時間
            $last_send_time = $user->send_time ;

            // 計算與連續兩次指令相差的秒數, 如果在指定秒數之內, 則持續階段, 反之, 從第一階段開始
            $phase = $send_time->diffInSeconds($last_send_time) < $send_time_diff_sec ? $user->phase : '' ;
        }
        else {
            $user = new Linebot ;
            $user->userId = $userId ;
            $phase = '' ;
        }
        // 本次指令送出時間
        $user->send_time = $send_time->toDateTimeString() ;
        
        // 堆疊本次指令
        $phase = ! empty($phase) ? preg_split('/,/', $phase) : [] ;
        array_push($phase, $text) ;
        //---------------------------------------------------------//

        // 處理訊息
        if ($eventsType === 'message') {

            $message = '' ;
            
            if ($phase[0] == 'member') {

                if (! isset($phase[1])) {
                    $message = "指令 'get' 取得會員, 指令 'delete' 刪除會員, 指令 'count' 會員總數" ;
                    $user->phase = join(',', $phase) ;
                }

                else {
                    if ($phase[1] == 'get') {
                        if (! isset($phase[2])) {
                            $message = '請輸入您要取得的會員帳號 ?' ;
                            $user->phase = join(',', $phase) ;
                        }
                        else {
                            if (Member::where('account', $phase[2])->count()) {
                                $data = Member::where('account', $phase[2])->get() ;
                                $message = '取得 ('.$phase[2].') 的會員資料: '. json_encode($data, JSON_UNESCAPED_UNICODE)  ;
                            }
                            else {
                                $message = '會員 ('. $phase[2] .') 不存在' ; 
                            }
                            $user->phase = '' ;
                        }
                    }
                    else if ($phase[1] == 'delete') {
                        if (! isset($phase[2])) {
                            $message = '請輸入您要刪除的會員帳號 ?' ;
                            $user->phase = join(',', $phase) ;
                        }
                        else {
                            if (Member::where('account', $phase[2])->count()) {
                                Member::where('account', $phase[2])->delete() ;
                                $message = '刪除 ('.$phase[2].') 的會員資料' ;
                            }
                            else {
                                $message = '會員 ('. $phase[2] .') 不存在' ; 
                            }
                            $user->phase = '' ;
                        }
                    }
                    else if ($phase[1] == 'count') {
                        $count = Member::all()->count() ;
                        $message = '註冊會員: '.$count.' 人' ;
                        $user->phase = '' ;
                    }
                    else {
                        $message = "指令 'get' 取得會員, 指令 'delete' 刪除會員, 指令 'count' 會員總數" ;
                        // 如果指令不正確, 則取出本次堆疊的指令: ['member', 'aaaa'] -> ['member']
                        array_pop($phase) ;
                        $user->phase = join(',', $phase) ;
                    }
                }
            } // End-$phase[0] == 'member'

            else if ($phase[0] == 'user') {
                if (! isset($phase[1])) {
                    $message = "指令 'get' 取得使用者, 指令 'delete' 刪除使用者, 指令 'count' 使用者總數" ;
                    $user->phase = join(',', $phase) ;
                }

                else {
                    if ($phase[1] == 'get') {
                        if (! isset($phase[2])) {
                            $message = '請輸入您要取得的使用者名稱 ?' ;
                            $user->phase = join(',', $phase) ;
                        }
                        else {
                            if (User::where('name', $phase[2])->count()) {
                                $data = User::where('name', $phase[2])->get() ;
                                $message = '取得 ('.$phase[2].') 的使用者資料: '. json_encode($data, JSON_UNESCAPED_UNICODE)  ;
                            }
                            else {
                                $message = '使用者 ('. $phase[2] .') 不存在' ; 
                            }
                            $user->phase = '' ;
                        }
                    }
                    else if ($phase[1] == 'delete') {
                        if (! isset($phase[2])) {
                            $message = '請輸入您要刪除的使用者名稱 ?' ;
                            $user->phase = join(',', $phase) ;
                        }
                        else {
                            if (User::where('name', $phase[2])->count()) {
                                User::where('name', $phase[2])->delete() ;
                                $message = '刪除 ('.$phase[2].') 的使用者資料' ;
                            }
                            else {
                                $message = '使用者 ('. $phase[2] .') 不存在' ; 
                            }
                            $user->phase = '' ;
                        }
                    }
                    else if ($phase[1] == 'count') {
                        $count = User::all()->count() ;
                        $message = '註冊人數: '.$count.' 人' ;
                        $user->phase = '' ;
                    }
                    else {
                        $message = "指令 'get' 取得使用者, 指令 'delete' 刪除使用者, 指令 'count' 使用者總數" ;
                        // 如果指令不正確, 則取出本次堆疊的指令: ['user', 'aaaa'] -> ['user']
                        array_pop($phase) ;
                        $user->phase = join(',', $phase) ;
                    }
                }
            } // End-$phase[0] == 'user'

            else if ($phase[0] == 'mail') {
                if (! isset($phase[1])) {
                    $message = "指令 'send' 寄信給使用者" ;
                    $user->phase = join(',', $phase) ;
                }
                
                else {
                    if ($phase[1] == 'send') {
                        if (! isset($phase[2])) {
                            $message = '請輸入您要寄發的電子郵件 ?' ;
                            $user->phase = join(',', $phase) ;
                        }
                        else {
                            $data = ["email" => $phase[2]] ;

                            // 檢驗電子郵件格式
                            $validator = Validator::make($data, [
                                'email' => 'required|email',
                            ]);

                            // 電子郵件格式錯誤, 重新此階段
                            if ($validator->fails()) {
                                $message = '電子郵件 ('.$phase[2].') 格式不正確, 請重新輸入' ;
                                array_pop($phase) ;
                                $user->phase = join(',', $phase) ;
                            }

                            // 寄發電子郵件的程式
                            else {
                                $smtp = config('henwen.smtp') ;
                                $mr = new LineChatbotRepository() ;
                                $mr->setBasic($smtp["host"], $smtp["port"], $smtp["login_user"], $smtp["login_passwd"]) ;
                                $mr->setFrom("henwen.work@gmail.com", "Line Chat Bot", "這是一封由 Line Chat Bot 觸發的測試信") ;
                                $mr->setPHPMailer($smtp["is_smtp"], $smtp["smtp_auth"], $smtp["smtp_debug"], $smtp["is_ssl"]) ;
                                $mr->send($data) ;

                                $message = '寄送電子郵件至 ('.$phase[2].')' ;
                                $user->phase = '' ;
                            }
                        }
                    }
                    else {
                        $message = "指令 'send' 寄信給使用者" ;
                        // 如果指令不正確, 則取出本次堆疊的指令: ['mail', 'aaaa'] -> ['mail']
                        array_pop($phase) ;
                        $user->phase = join(',', $phase) ;
                    }
                }
            } // End-$phase[0] == 'mail'

            else {
                $message = "指令 'member' 會員管理, 指令 'user' 使用者管理, 指令 'mail' 信件通知" ;
            }

            // 儲存使用者狀態: phase, send_time
            $user->save() ;

            // 送出訊息
            if (! empty($message)) {
                $data = [
                    'replyToken' => $replyToken,
                    'messages' => [
                        [
                            'type' => 'text',
                            'text' => $message,
                        ]
                    ]
                ] ;

                // Reply API
                $ch = curl_init() ;
                curl_setopt($ch, CURLOPT_URL, 'https://api.line.me/v2/bot/message/reply') ;
                curl_setopt($ch, CURLOPT_POST, true) ;
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ;
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)) ;
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' .config('line.channel_access_token')
                ]) ;
                $result = curl_exec($ch) ;
                curl_close($ch) ;
            } // End 送出訊息
        } // End $eventsType === 'message'

    } // End reply()
}
