<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DriverDetail extends Model
{
    protected $table = 'driver_details';
    protected $fillable = ['nik', 'program_year','name','address', 'photo','created_by','updated_by','deleted_by','created_at','updated_at','deleted_at'];
}