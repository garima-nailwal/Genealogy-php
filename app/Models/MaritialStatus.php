<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaritialStatus extends Model
{
    protected $table = 'maritial_status'; // exact table name
    protected $fillable = ['id','maritial_status','description'];    
    public $timestamps = false;
}
