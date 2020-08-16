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

		/**
		 * 取得會員分頁資料
		 * @param $perpage INT 分頁數量, e.g. 10
		 */
		public static function getMemberPaginate($perpage = 10)
		{
			return DB::table('member AS u')
			->leftJoin('user_group AS ug', 'u.user_id', '=', 'ug.user_id')
			->leftJoin('groups AS g', 'ug.group_id', '=', 'g.group_id')
			->groupBy('u.user_id')
			->select('u.*', DB::raw('GROUP_CONCAT(g.group_name) AS group_name'))
			->paginate($perpage) ;
		}
	}