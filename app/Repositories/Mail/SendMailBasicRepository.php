<?php
	namespace App\Repositories\Mail;

	use Illuminate\Support\Facades\Log;
    use PHPMailer\PHPMailer\PHPMailer;

	/**
     * 寄信程式基礎類別，寄信必須繼承此類別。
     *
     */
    class SendMailBasicRepository {
        /**
         * @var Object, PHPMailer 物件
         */
        protected $mail_obj ;

        /**
         * @var String, SMTP 主機
         */
        protected $host ;

        /**
         * @var String, SMTP 驗證帳號
         */
        protected $login_user ;

        /**
         * @var String, SMTP 驗證密碼
         */
        protected $login_passwd ;

        /**
         * @var String, 起始時間, e.g. 2015-11-20
         */
        protected $start_time ;

        /**
         * @var String, 終點時間, e.g. 2015-11-24
         */
        protected $end_time ;

        /**
         * @var String, 寄件人姓名, e.g. Log Service
         */
        protected $from_username ;

        /**
         * @var String, 信件標題, e.g. 本週 Log 通知
         */
        protected $subject ;

        /**
         * @var Array, 寄信所需資料陣列
         */
        protected $data ;

        /**
         * 建構式：設定 SMTP 帳號密碼、寄信者資訊、信件標題
         * @param $host String 主機
         * @param $port INT 連接埠號
         * @param $login_user String, SMTP IP
         * @param $login_passwd String, Password
         * @return Object
         */
        public function setBasic($host = "", $port = 25, $login_user = "", $login_passwd = "") {
            $this->mail_obj      = new PHPMailer() ;
            $this->host          = $host ;         // SMTP 伺服器
            $this->port          = (int) $port ;   // SMTP 伺服器 port
            $this->login_user    = $login_user ;   // 驗證姓名
            $this->login_passwd  = $login_passwd ; // 驗證密碼
            $this->from          = "" ; // 寄件者信箱
            $this->from_username = "" ; // 寄件者姓名
            $this->subject       = "" ; // 信件標題
        }

        /**
         * 設定信件寄件者與信件標題
         * @param $from String, 寄件者信箱
         * @param $from_username String, 寄件者姓名
         * @param $subject String, 信件標題
         */
        public function setFrom($from = "", $from_username = "Mail Service", $subject = "本週通知")
        {
            $this->from = $from ;
            $this->from_username = $from_username ;
            $this->subject = $subject ;
        }

        /**
         * 設定 PHPMailer 物件內容
         * @param $isSMTP Boolean, IsSMTP() method
         * @param $SMTPAuth Boolean, 是否要帳號密碼認證
         * @param $SMTPDebug INT, Debug Mode
         */
        public function setPHPMailer($isSMTP = false, $SMTPAuth = false, $SMTPDebug = 2, $isSSL = false)
        {
            if ($isSMTP) {
                $this->mail_obj->IsSMTP() ; // 設定使用SMTP方式寄信
            }

            // If SSL
            if ($isSSL) {
                $this->mail_obj->SMTPSecure = "ssl" ; // Gmail的SMTP主機需要使用SSL連線
            }

            $this->mail_obj->SMTPAuth = $SMTPAuth ;   // 設定 SMTP 是否需要驗證
            $this->mail_obj->SMTPDebug = $SMTPDebug ; // 如果有需要可以開啟SMTPDebug模式，可以看到所有連線資訊
            $this->mail_obj->Host = $this->host ;     // Gamil的SMTP主機
            $this->mail_obj->Port = $this->port ;     // Gamil的SMTP主機的埠號 (Gmail為465)。
            $this->mail_obj->CharSet = "utf-8" ;      // 郵件編碼
            $this->mail_obj->WordWrap = 50 ;          // 每50個字元自動斷行
            
            // 如果 SMTP 要認證就要設定帳號密碼
            if ($SMTPAuth) {
                $this->mail_obj->Username = $this->login_user ;   // 驗證帳號Gamil帳號
                $this->mail_obj->Password = $this->login_passwd ; // 驗證密碼 Gmail密碼
            }

            $this->mail_obj->From = $this->from ;               // 寄件者信箱
            $this->mail_obj->FromName = $this->from_username ;  // 寄件者姓名
            $this->mail_obj->Subject = $this->subject ;         // 郵件標題
            $this->mail_obj->IsHTML(true) ; // 使用 HTML
        }

        /**
         * 設定資料、Mail 主體、Address & 寄出信件
         * @param $data Array, 資料陣列, 必須包含收件者信箱 email 欄位
         */
        public function send($data = array())
        {
            // 設定資料陣列
            $this->data = $data ;
            // 設定信件主體內容
            $this->setBody() ;
            // 設定信件收件者
            $this->setAddress() ;
            // 寄信
            $this->sendMail() ;
        }

        /**
         * 設定信件主體內容，讓子類別重載。
         */
        public function setBody() {}

        /**
         * 設定收信人電子郵件，讓子類別重載。
         */
        public function setAddress() {}

        /**
         * 寄信：設定接收的 email，再寄出
         */
        public function sendMail()
        {
            if ($this->mail_obj->Send()) {
            	Log::info("寄信成功", array("email" => $this->data["email"])) ;
            }
            else {
               Log::error("寄信失敗", array("email" => $this->data["email"])) ;
               //echo $this->mail_obj->ErrorInfo;
            }
        }
    }