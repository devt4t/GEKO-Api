<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PraLandOwnership extends Model
{
    protected $table = 'pra_land_ownerships';
    
    protected $fillable = ['pra_no', 'type_ownership', 'land_ownership', 'percentage', 'created_at', 'updated_at'];
}