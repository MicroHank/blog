<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Events\UserSavedEvent;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Get the user_id, group_id for the User.
     */
    public function groupid()
    {
        return $this->hasMany('App\Models\UserGroup');
    }

    public static function getUserPaginate($perpage = 10)
    {
        return DB::table('users')->paginate($perpage) ;
    }
}
