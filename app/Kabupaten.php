<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Kabupaten extends Model
{
    protected $table = 'kabupatens';
    protected $fillable = ['kabupaten_no','province_code', 'name', 'kab_code' , 'created_at','updated_at'];
}
