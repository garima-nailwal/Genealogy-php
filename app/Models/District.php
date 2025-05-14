<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $table = 'districts'; // exact table name
    protected $fillable = ['id','district'];    
    public $timestamps = false;
}
