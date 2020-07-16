<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberInformation extends Model
{
    protected $table = 'member_information';
    public $primaryKey = 'user_id';
}
