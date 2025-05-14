<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Caste extends Model
{
    protected $table = 'castes'; // exact table name
    protected $fillable = ['id','caste_name','description','religion_id'];    
    public $timestamps = false;
}
