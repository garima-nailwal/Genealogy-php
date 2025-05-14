<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Relative extends Model
{
    protected $table = 'relatives'; 
    protected $fillable = ['id','primary_user_id','relative_id','relationship_id'];    
    public $timestamps = false;

     // Define relationships
     public function parent()
     {
         return $this->belongsTo(User::class, 'primary_user_id');
     }
 
     public function relative()
     {
         return $this->belongsTo(User::class, 'relative_id');
     }
 
     public function relationshipType()
     {
         return $this->belongsTo(Relationship::class, 'relationship_id');
     }
}
