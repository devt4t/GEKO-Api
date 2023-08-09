<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaDesa extends Model
{
    protected $table = 'ta_desas';
    protected $fillable = ['area_code', 'kode_desa','program_year', 'active', 'created_at','updated_at'];
}
