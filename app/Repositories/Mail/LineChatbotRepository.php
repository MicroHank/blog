<?php
    namespace App\Repositories\Mail;

    use App\Repositories\Mail\SendMailBasicRepository;
    
    class LineChatbotRepository extends SendMailBasicRepository {
        /**
         * 建構式
         */
         public function __construct() {}

         /**
         * 設定收件者信箱
         * 重載父類別的 setAddress()
         */
        public function setAddress()
        {
            // 清除所有的收件人電子郵件設定
            $this->mail_obj->ClearAllRecipients() ;
            
            // 設定收件人信箱
            $this->mail_obj->AddAddress($this->data["email"]) ;
        }

        /**
         * 設定信件主體
         * 重載父類別的 setBody(): 資料會儲存在 $this->data
         */
         public function setBody()
         {
             // 信件主體
             $this->mail_obj->Body = "" ;
             $this->mail_obj->Body .= '<span style="font-size:20px; color:#483D8B">From Line Chat Bot</span><br />' ;
             $this->mail_obj->Body .= '<h3>Hello World</h3>' ;
         }
     }