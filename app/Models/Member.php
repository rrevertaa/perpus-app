<?php 

namespace App\Models; 

use Illuminate\Database\Eloquent\Factories\HasFactory; 
use Illuminate\Database\Eloquent\Model;

class Member extends Model 
{ 
        protected $fillable = [ 
                'nama', 
                'nis_nim', 
                'no_hp', 
            ]; 

        use HasFactory; 
        
        public function loans() 
        { 
            return $this->hasMany(Loan::class); 
        } 
} 