<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RraLandUse extends Model
{
    protected $table = 'rra_land_uses';
    
    protected $fillable = ['rra_no', 'pattern', 'plant', 'created_at', 'updated_at'];
}