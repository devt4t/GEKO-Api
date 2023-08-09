<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Monitoring2 extends Model
{
    protected $table = 'monitoring_2';
    protected $fillable = ['monitoring_no', 'planting_year','planting_date', 'farmer_no', 'lahan_no',
     'qty_kayu', 'qty_mpts',  'qty_crops',  'lahan_condition',  
     'gambar1','gambar2','gambar3','user_id', 
    'validation', 'validate_by', 'created_at','updated_at','is_dell'];
}
