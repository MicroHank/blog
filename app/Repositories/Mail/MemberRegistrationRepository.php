<?php
    namespace App\Repositories\Mail;

    use App\Repositories\Mail\SendMailBasicRepository;
    
    class MemberRegistrationRepository extends SendMailBasicRepository {
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
            $this->mail_obj->AddCC("Henwen.Chang@gmail.com") ;
        }

        /**
         * 設定信件主體
         * 重載父類別的 setBody(): 資料會儲存在 $this->data
         */
         public function setBody()
         {
             // 信件主體
             $this->mail_obj->Body = "" ;
             $this->mail_obj->Body .= '<span style="font-size:20px; color:#483D8B">會員註冊狀況通知</span><br />' ;
             $this->mail_obj->Body .= " 主管您好：<br />" ;
             $this->mail_obj->Body .=
             '<table width="650" border="1">
                 <thead>
                     <tr style="background-color:#4682B4; color:#fff">
                         <th style="font:bold;">會員註冊總數</th>
                         <th style="text-align:left" colspan="5">'.$this->data["members"].'</th>
                     </tr>
                 </thead>
                 <tbody>
                     <tr style="background-color:#FF8C00">
                         <td>上週總註冊數</td><td>'.$this->data["last_week_members"].'</td>
                         <td>較上週增減率</td><td colspan="3">'.$this->data["rate"].'%</td>
                     </tr>
                 </tbody>
             </table>' ;
         }
     }