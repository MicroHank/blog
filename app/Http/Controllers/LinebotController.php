<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

            // 回到第一階段
            if (in_array($text, ['re', 'menu'])) {
                $phase = [$text] ;
            }
            else {
                // 堆疊本次指令
                $phase = ! empty($phase) ? preg_split('/,/', $phase) : [] ;
                array_push($phase, $text) ;
            }
            //---------------------------------------------------------//

            $message = '' ;
            
            if ($phase[0] == 'member') {

                if (! isset($phase[1])) {
                    $message = $line_chatbot_rep->getMemberMenu() ;
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
                                $message = '取得 ('.$phase[2].') 的會員資料:'."\n" ;
                                $message .= json_encode($data, JSON_UNESCAPED_UNICODE) ;
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
                        $message = '註冊會員: ' .Member::all()->count(). ' 人' ;
                        $user->phase = '' ;
                    }
                    else {
                        $message = $line_chatbot_rep->getMemberMenu() ;
                        // 如果指令不正確, 則取出本次堆疊的指令: ['member', 'aaaa'] -> ['member']
                        array_pop($phase) ;
                        $user->phase = join(',', $phase) ;
                    }
                }
            } // End-$phase[0] == 'member'

            else if ($phase[0] == 'user') {
                if (! isset($phase[1])) {
                    $message = $line_chatbot_rep->getUserMenu() ;
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
                                $message = '取得 ('.$phase[2].') 的使用者資料:'."\n" ;
                                $message .= json_encode($data, JSON_UNESCAPED_UNICODE)  ;
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
                        $message = '註冊人數: ' .User::all()->count(). ' 人' ;
                        $user->phase = '' ;
                    }
                    else {
                        $message = $line_chatbot_rep->getUserMenu() ;

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
                                $from_mail = "henwen.work@gmail.com" ;
                                $from_user = "Henwen's Line Chatbot" ;
                                $from_title = "這是一封由 Line Chatbot 觸發的測試信" ;

                                $smtp = config('henwen.smtp') ;
                                $mr = new LineChatbotRepository() ;
                                $mr->setBasic($smtp["host"], $smtp["port"], $smtp["login_user"], $smtp["login_passwd"]) ;
                                $mr->setFrom($from_mail, $from_user, $from_title) ;
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

            else if ($phase[0] == 'server') {
                if (! isset($phase[1])) {
                    $message = $line_chatbot_rep->getServerMenu() ;
                    $user->phase = join(',', $phase) ;
                }
                
                else {
                    if ($phase[1] == 'disk') {
                        $total_bytes = disk_total_space('C:') ;
                        $free_bytes = disk_free_space('C:') ;
                        $total_gb = $total_bytes ? round($total_bytes / pow(1024, 3), 2) : 0 ;
                        $free_gb = $free_bytes ? round($free_bytes / pow(1024, 3), 2) : 0 ;
                        $message = '本機硬碟(C:)'."\n".'剩餘 '. $free_gb .' GB, 共 '. $total_gb .' GB'."\n" ;

                        $total_bytes = disk_total_space('D:') ;
                        $free_bytes = disk_free_space('D:') ;
                        $total_gb = $total_bytes ? round($total_bytes / pow(1024, 3), 2) : 0 ;
                        $free_gb = $free_bytes ? round($free_bytes / pow(1024, 3), 2) : 0 ;
                        $message .= '本機硬碟(D:)'."\n".'剩餘 '. $free_gb .' GB, 共 '. $total_gb .' GB' ;

                        $user->phase = '' ;
                    }
                    else if ($phase[1] == 'task') {
                        exec('tasklist /FI "Status eq Running" /FO csv 2>NUL', $task_list) ;
                        array_shift($task_list) ;
                        $message = '映像名稱, RAM使用量'."\n";
                        foreach ($task_list as $task) {
                            $data = preg_split('/,/', $task) ;
                            $message .= '('. $data[0] .', '. $data[count($data)-1]. ")\n";
                        }

                        $user->phase = '' ;
                    }
                    else {
                        $message = $line_chatbot_rep->getServerMenu() ;
                        // 如果指令不正確, 則取出本次堆疊的指令
                        array_pop($phase) ;
                        $user->phase = join(',', $phase) ;
                    }
                }
            } // End-$phase[0] == 'server'

            else if ($phase[0] == 'sql') {
                if (! isset($phase[1])) {
                    $message = "請寫不含分號 ; 的 SQL 語句" ;
                    $user->phase = join(',', $phase) ;
                }
                else {
                    try {
                        if (strpos($phase[1], ';') !== false) {
                            throw new \Exception('不能包含分號', 0) ;
                        }
                        if (strpos($phase[1], 'update') === 0 || strpos($phase[1], 'delete') === 0) {
                            throw new \Exception('請勿執行 Update 或 Delete 語句', 0) ;
                        }
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

            else if (in_array($phase[0], ['movie', '電影'])) {
                if (! isset($phase[1])) {
                    $message = $line_chatbot_rep->getMovieMenu() ;
                    $user->phase = join(',', $phase) ;
                }
                
                else {
                    if ($phase[1] == '1') {
                        $url = 'https://movies.yahoo.com.tw/theater_result.html/id=129' ;
                        $message = $line_chatbot_rep->getMovieMessage($url) ;
                        $user->phase = '' ;
                    }
                    else if ($phase[1] == '2') {
                        $url = 'https://movies.yahoo.com.tw/theater_result.html/id=12' ;
                        $message = $line_chatbot_rep->getMovieMessage($url) ;
                        $user->phase = '' ;
                    }
                    else {
                        $message = $line_chatbot_rep->getMovieMenu() ;
                        // 如果指令不正確, 則取出本次堆疊的指令
                        array_pop($phase) ;
                        $user->phase = join(',', $phase) ;
                    }
                }
            } // End-$phase[0] == 'movie', '電影'

            else if (in_array($phase[0], ['gold', '黃金'])) {
                include_once base_path('public/phplib/simple_html_dom.php') ;
                $dom = file_get_html("https://www.esunbank.com.tw/bank/personal/deposit/gold/price/current-price") ;
                $result = $dom->find('table[class=inteTable] td') ;
                $message = [
                    "ounce" => ['buy' => $result[4]->innertext, 'sell' => $result[5]->innertext],
                    "gram"  => ['buy' => $result[10]->innertext, 'sell' => $result[11]->innertext],
                ] ;

                $user->phase = '' ;
            } // End-$phase[0] == 'sql'

            else {
                $message = $line_chatbot_rep->getMenu() ;
                $user->phase = '' ;
            }

            // 儲存使用者狀態: phase, send_time
            $user->save() ;

        } // End $eventsType === 'message'

        // 送出訊息
        if (! empty($message)) {
            // 套用 Flex Message
            if (in_array($phase[0], ['gold', '黃金'])) {
                $flex_messages = $line_chatbot_rep->getGoldFlexMessage($message) ;
            }
            else if (in_array($phase[0], ['movie', '電影']) && is_array($message)) {
                $flex_messages = $line_chatbot_rep->getMovieFlexMessage($message) ;
            }
            else {
                $flex_messages = $line_chatbot_rep->getFlexMessage($message) ;
            }
            
            // 回覆訊息給 Line User
            $line_chatbot_rep->sendMessage($flex_messages, $replyToken) ;
        } // End 送出訊息

    } // End reply()

}
