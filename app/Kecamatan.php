<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Kecamatan extends Model
{
    protected $table = 'kecamatans';
    protected $fillable = ['kabupaten_no', 'name', 'kode_kecamatan', 'created_at','updated_at'];
}
