<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Religion extends Model
{
    protected $table = 'religions'; // exact table name
    protected $fillable = ['id','religion_name','description'];    
    public $timestamps = false;
}