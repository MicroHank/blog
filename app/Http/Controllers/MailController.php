<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Mail\MemberRegistrationRepository;

class MailController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function send(MemberRegistrationRepository $mr)
    {
        $data = [
            "email" => "henwen.work@gmail.com",
            "members" => 100,
            "last_week_members" => 123,
            "rate" => 52,
        ] ; 
        $smtp = config('henwen.smtp') ;
        $mr->setBasic($smtp["host"], $smtp["port"], $smtp["login_user"], $smtp["login_passwd"]) ;
        $mr->setFrom("henwen.chang@gmail.com", "每週報表", "這是一封 SMTP Mail 測試信") ;
        $mr->setPHPMailer($smtp["is_smtp"], $smtp["smtp_auth"], $smtp["smtp_debug"], $smtp["is_ssl"]) ;
        $mr->send($data) ;
    }
}
