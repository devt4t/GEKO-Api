<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TargetArea extends Model
{
    protected $table = 'target_areas';
    protected $fillable = ['area_code', 'name','mu_no', 'active', 'kab_code','fc_no','kabupaten_no',  'province_code','luas', 'created_at','updated_at', 'program_year'];
}
