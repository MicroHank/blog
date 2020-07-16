<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Events\MemberSavedEvent;
use App\Events\MemberDeletedEvent;
use Illuminate\Support\Facades\DB;

class Member extends Model
{
    protected $table = 'member';
    public $primaryKey = 'user_id';
    protected $fillable = ['account', 'user_name', 'password', 'supervisor_id'];
    protected $hidden = ['password'];
    public $timestamps = false;
    
    // Model Save and Delete will Fire these Events, to do log
    protected $events = [
        'saved' => MemberSavedEvent::class,
        'deleted' => MemberDeletedEvent::class,
    ];

    /**
     * Get the Groups for the User.
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
