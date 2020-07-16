<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Repositories\Member\MemberRepository;
use App\Models\User;
use App\Models\Member;

class ApiController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(MemberRepository $mr, Request $request)
    {
        $this->member_rep = $mr ;
        // 檢驗 Api Token 
        $api_token = $request->input('api_token') ;
        $this->verifyToken($api_token) ;
    }

    /**
     * API: 檢驗 API Token 是否存在或過期
     *
     * @return \Illuminate\Http\Response
     */
    public function verifyToken($api_token)
    {
        $user = User::where('api_token', $api_token)->first() ;
        
        // Token does not exist
        if (empty($user)) {
            echo response()->json([
                'status' => 0,
                'message' => 'API Token is invalid',
                'data' => [],
            ])->getContent() ;
            exit ;
        }

        // User check: status = 1 or deleted_at = null
        if ($user->status !== 1 || $user->deleted_at !== null) {
            echo response()->json([
                'status' => 0,
                'message' => 'User is invalid',
                'data' => [],
            ])->getContent() ;
            exit ;
        }

        // Token exists but Token is Expired
        if ($user->api_token_expired < Carbon::now()) {
            echo response()->json([
                'status' => 0,
                'message' => 'API Token is expired',
                'data' => [],
            ])->getContent() ;
            exit ;
        }
    }

    /**
     * API: 取得會員清單
     *
     * @return \Illuminate\Http\Response
     */
    public function getAllMember()
    {
        $member_list = $this->member_rep->getAllMember() ;
        return response()->json([
            'status' => 1,
            'message' => 'Success',
            'data' => json_decode(json_encode($member_list, JSON_UNESCAPED_UNICODE), true),
        ])->getContent() ;
    }
}
