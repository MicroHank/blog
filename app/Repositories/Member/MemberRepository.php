<?php
	namespace App\Repositories\Member;

	use Illuminate\Support\Facades\DB;

	class MemberRepository
	{
		public function __construct() {}

		public function getFirstMemberById($id)
		{
			return DB::table('member')->where('user_id', '=', $id)->first() ;
		}

		public function getAllMember()
		{
			return DB::table('member AS m')->leftJoin('member_information AS mi', 'm.user_id', '=', 'mi.user_id')
			->select('m.user_id', 'm.account', 'm.user_name', 'mi.email', 'mi.address', 'mi.jobtitle', 'mi.birthday', 'mi.created_at')
			->get() ;
		}
	}