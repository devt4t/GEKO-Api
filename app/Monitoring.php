<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Monitoring extends Model
{
    protected $table = 'monitorings';
    protected $fillable = ['monitoring_no', 'planting_year','planting_date', 'farmer_no', 'lahan_no', 'type_sppt', 'qty_kayu', 'qty_mpts',  'qty_crops', 'qty_std', 'lahan_condition', 'gambar1','gambar2','gambar3','user_id',  'validation', 'validate_by', 'created_at','updated_at','is_dell'];
}
