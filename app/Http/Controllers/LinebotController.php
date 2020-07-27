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
use App\Repositories\Mail\LineChatbotRepository;
use App\Repositories\Line\LineChatbotMessageRepository;

class LinebotController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {}

    /**
     * Line Bot callback URL
     */
    public function reply(LineChatbotMessageRepository $line_chatbot_rep)
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
        $replyToken = $obj['events'][0]['replyToken'] ; // 回應的 Token, 用於 reply api
        $userId = isset($obj['events'][0]['source']['userId']) ? $obj['events'][0]['source']['userId'] : 0 ; // 發送訊息的使用者編號
        $groupId = isset($obj['events'][0]['source']['groupId']) ? $obj['events'][0]['source']['groupId'] : 0 ; // 在哪個群組發送
        $type = $obj['events'][0]['source']['type'] ; // 回傳值: user, group

        if ($eventsType == 'postback') {
            $postback_data = $obj['events'][0]['postback']['data'] ;
            Log::info($postback_data) ;
        }
        else if ($eventsType == 'message') {
            $text = strtolower(trim($obj['events'][0]['message']['text'])) ; // 使用者傳送的訊息
            Log::info($text) ;    
        }
        //---------------------------------------------------------//
        
        // 處理訊息
        if ($eventsType === 'postback') {
            $message = $postback_data ;
        }

        else if ($eventsType === 'message') {
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

            $message = '' ;
            
            if ($phase[0] == 'member') {

                if (! isset($phase[1])) {
                    $message = "指令 'get' 取得會員\n指令 'delete' 刪除會員\n指令 'count' 會員總數" ;
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
                                $message = '取得 ('.$phase[2].') 的會員資料:'."\n". json_encode($data, JSON_UNESCAPED_UNICODE)  ;
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
                        $message = "指令 'get' 取得會員\n指令 'delete' 刪除會員\n指令 'count' 會員總數" ;
                        // 如果指令不正確, 則取出本次堆疊的指令: ['member', 'aaaa'] -> ['member']
                        array_pop($phase) ;
                        $user->phase = join(',', $phase) ;
                    }
                }
            } // End-$phase[0] == 'member'

            else if ($phase[0] == 'user') {
                if (! isset($phase[1])) {
                    $message = "指令 'get' 取得使用者\n指令 'delete' 刪除使用者\n指令 'count' 使用者總數" ;
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
                                $message = '取得 ('.$phase[2].') 的使用者資料:'."\n". json_encode($data, JSON_UNESCAPED_UNICODE)  ;
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
                        $message = "指令 'get' 取得使用者\n指令 'delete' 刪除使用者\n指令 'count' 使用者總數" ;
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

            else if ($phase[0] == 'sql') {
                if (! isset($phase[1])) {
                    $message = "請寫不含分號 ; 的有效的 SQL (SELECT Only): " ;
                    $user->phase = join(',', $phase) ;
                }
                else {
                    try {
                        // 需要檢查與過濾 SQL 語法
                        $result = DB::select($phase[1]) ;
                        $message = json_encode($result, JSON_UNESCAPED_UNICODE) ;
                    } catch (\Exception $e) {
                        $message = json_encode($e->getMessage(), JSON_UNESCAPED_UNICODE) ;
                    } finally {
                        $user->phase = '' ;
                    }
                }
            } // End-$phase[0] == 'sql'

            else {
                $message = "指令 'member' 會員管理\n指令 'user' 使用者管理\n指令 'mail' 信件通知\n指令 'sql' 可執行 SQL 語法" ;
            }

            // 儲存使用者狀態: phase, send_time
            $user->save() ;
        } // End $eventsType === 'message'

        // 送出訊息
        if (! empty($message)) {

             // 套用 Flex Message
            $flex_messages = $line_chatbot_rep->getFlexMessage($message) ;

            // 回覆訊息給 Line User
            $line_chatbot_rep->sendMessage($flex_messages, $replyToken) ;

        } // End 送出訊息

    } // End reply()
}
