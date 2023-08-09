<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class VillageBorder extends Model
{
    protected $table = 'village_borders';
    
    protected $fillable = ['rra_no', 'point', 'border_type', 'kabupaten_no', 'kode_kecamatan', 'kode_desa', 'created_at', 'updated_at' ];
}