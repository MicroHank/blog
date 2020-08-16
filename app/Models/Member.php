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

}
