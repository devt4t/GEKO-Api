<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PraDryLandSpread extends Model
{
    protected $table = 'pra_dry_land_spreads';
    
    protected $fillable = ['pra_no', 'dusun_name', 'type_utilization', 'created_at', 'updated_at'];
}