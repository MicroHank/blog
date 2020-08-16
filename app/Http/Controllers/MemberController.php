<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Http\Requests\StoreMemberRequest;
use Validator;
use App\Repositories\Member\MemberRepository;
use App\Models\Member;
use App\Models\MemberLogs;

class MemberController extends Controller
{
    public function __construct(MemberRepository $mr)
    {
        $this->middleware('language') ;
        $this->member_rep = $mr ;
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $perpage = config('henwen.paginate.member.list', 10) ;
        $member = $this->member_rep->getMemberPaginate($perpage) ;
        return view('member.index', ['member' => $member ]) ;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        return view('member.create') ;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreMemberRequest $request)
    {
        /*
         * 利用 Validator 檢驗參數, 失敗時會帶 $errors 至 View
         */
        // $validator = Validator::make($request->all(), [
        //     'account' => 'required|min:3|max:12',
        //     'password1' => 'required|min:3|max:10',
        //     'password2' => 'required|min:3|max:10|same:password1',
        //     'username' => 'required|min:3|max:20',
        // ]);

        // if ($validator->fails()) {
        //     $request->flashExcept(['password1', 'password2']) ;
        //     return redirect()
        //         ->route('member.create')->withErrors($validator)
        //         ->withInput()->with('status', '帳號長度必須大於 3') ;
        // }

        // 手動檢查
        // 檢查密碼是否相等, 導回新增會員頁面 並帶入原來的 $request 以及新增 session 變數 status
        // if ($password1 !== $password2) {
        //     $request->flashExcept(['password1', 'password2']) ;
        //     return redirect()->route('member.create')->withInput()->with('status', '密碼欄位必須相等') ;
        // } 

        // StoreMemberRequest 驗證通過後
        $account = $request->input('account') ;
        $password1 = $request->input('password1') ;
        $password2 = $request->input('password2') ;
        $user_name =  $request->input('username') ;

        try {
            $m = new Member ;
            $m->account = $account ;
            $m->password = password_hash($password2, PASSWORD_BCRYPT, ["cost" => 12]) ;
            $m->user_name = $user_name ;
            $m->supervisor_id = 0 ;
            $m->save() ;

            return redirect()->route('member.index') ;
        }  catch (\Exception $e) {
            $request->flashExcept(['password1', 'password2']) ;
            return redirect()->route('member.create')->withInput()->with('status', $e->getMessage()) ;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $member = $this->member_rep->getFirstMemberById($id) ;
        return view('member.show', ['member' => $member ]) ;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $member = $this->member_rep->getFirstMemberById($id) ;
        return view('member.edit', ['member' => $member ]) ;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user_id = $id ;
        $password1 = $request->input('password1') ;
        $password2 = $request->input('password2') ;
        $user_name = $request->input('username') ;

        try {
            if ($password1 !== $password2) {
                throw new \Exception("Password are not equal.\r\n", 1) ;
            }
            $password_hash = password_hash($password2, PASSWORD_BCRYPT, ["cost" => 12]) ;

            if (DB::table('member')->where('user_id', '=', $id)->update(['user_name' => $user_name, 'password' => $password_hash])) {
               return redirect()->route('member.index') ; 
            }
            else {
                echo "Error" ;
            }
        }  catch (\Exception $e) {
            echo "Add User Fail: ".$e->getMessage() ;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (Member::find($id)->delete()) {
            return redirect()->back() ;
        }
        else {
            return redirect()->back()->with('status', 'Something Wrong') ; 
        }
    }

    /**
     * Management Log.
     *
     * @return \Illuminate\Http\Response
     */
    public function log(Request $request)
    {
        $perpage = config('henwen.paginate.member.log', 10) ;
        $member_logs = MemberLogs::paginate($perpage) ;
        return view('member.log', ['member_logs' => $member_logs]) ;
    }
}
