<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TruckDetail extends Model
{
    protected $table = 'truck_details';
    protected $fillable = ['plat_no', 'program_year','status','type','min_capacity','max_capacity','nursery','active_date','is_active','created_by','updated_by','deleted_by','created_at','updated_at','deleted_at'];
}