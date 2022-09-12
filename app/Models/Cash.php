<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cash extends Model
{
    protected $fillable = ['user_id','name','slug','amount','description','when'];
    protected $dates = ['when'];
    
    use HasFactory;

}
