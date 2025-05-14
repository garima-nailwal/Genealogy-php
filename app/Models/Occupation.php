<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Occupation extends Model
{
    protected $table = 'occupation'; // exact table name
    protected $fillable = ['id','occupation_name','description'];    
    public $timestamps = false;
}
