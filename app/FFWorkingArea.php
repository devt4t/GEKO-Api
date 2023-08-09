<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FFWorkingArea extends Model
{
    protected $table = 'ff_working_areas';
    protected $fillable = ['ff_no', 'mu_no', 'area_code','kode_desa','program_year', 'created_at','updated_at'];
}