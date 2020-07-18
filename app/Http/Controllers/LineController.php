<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Repositories\Member\MemberRepository;
use App\Models\User;
use App\Models\Member;
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
        return view('line.index') ;
    }

    public function getCode(Request $request)
    {
        if ($request->has('code')) {
            $code = $request->input('code', '') ;
            $line = Line::find(1) ;
            $line->code = $code ;
            $line->save() ;

            $client_id = $line->client_id ;
            $client_secret = $line->client_secret ;

            $url = "https://notify-bot.line.me/oauth/token" ;

            $field = [
                'grant_type' => 'authorization_code',
                'redirect_uri' => 'http://127.0.0.1/laravel/blog/public/line/getCode',
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'code' => $code,
            ] ;

            $curlobj = curl_init() ;
            curl_setopt($curlobj, CURLOPT_RETURNTRANSFER, true) ;
            curl_setopt($curlobj, CURLOPT_TIMEOUT, 10) ;
            curl_setopt($curlobj, CURLOPT_POST, true);
            curl_setopt($curlobj, CURLOPT_URL, $url."?".http_build_query($field)) ;
            $output = curl_exec($curlobj) ;
            $result = json_decode($output, true) ;

            if ($result['status'] == 200) {
                $result['access_token'] ;

                $line = Line::find(1) ;
                $line->access_token = $result['access_token'] ;
                $line->save() ;

                return redirect()->route('line.index')->with('message', 'Get Access Token OK.') ;
            }
            else {
                return redirect()->route('line.index')->with('message', 'Get Access Token Fail.') ;
            }
        }
    }

    public function sendNotify(Request $request)
    {
        $message = $request->input('message') ;

        $line = Line::find(1) ;
        $access_token = $line->access_token ;

        $url = 'https://notify-api.line.me/api/notify' ;
        $field = [
            'message' => $message,
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

    public function checkAccessToken()
    {
        $line = Line::find(1) ;
        $access_token = $line->access_token ;

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

        $result = json_decode($output, true) ;
        return redirect()->back()->with('message', $result['message'].','.$result['targetType'].','.$result['target']) ;
    }
}
