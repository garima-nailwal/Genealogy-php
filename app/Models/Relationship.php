<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Relationship extends Model
{
    protected $table = 'relationship'; 
    protected $fillable = ['id','category','relationship_type','marathi_translation'];    
    public $timestamps = false;
}
