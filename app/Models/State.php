<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $table = 'state'; // exact table name
    protected $fillable = ['id','state','country'];    
    public $timestamps = false;
}
