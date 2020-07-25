<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Linebot extends Model
{
    protected $table = 'linebot';
    public $primaryKey = 'userId';
    protected $keyType = 'string';
    public $timestamps = false;
}
