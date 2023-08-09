<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LahanTutupan extends Model
{
    protected $table = 'lahan_tutupans';
    protected $fillable = ['lahan_no', 'land_area', 'planting_area', 'program_year', 'tutupan_lahan', 'pattern', 'created_at', 'updated_at'];
}
