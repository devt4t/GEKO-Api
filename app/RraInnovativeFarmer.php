<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RraInnovativeFarmer extends Model
{
    protected $table = 'rra_innovative_farmers';
    
    protected $fillable = ['rra_no', 'farmer_name', 'specialitation', 'potential', 'description', 'created_at', 'updated_at'];
}