<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LahanUmumMonitoring extends Model
{
    protected $table = 'lahan_umum_monitorings';
    protected $fillable = ['monitoring_no', 'program_year','planting_date', 'lahan_no', 'qty_kayu', 'qty_mpts',  'qty_crops', 'qty_std', 'lahan_condition', 'photo1','photo2','photo3','created_by', 
    'is_verified', 'verified_by', 'created_at','updated_at','is_dell', 'mou_no'];
}
