<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PlantingSocializationsPeriod extends Model
{
    protected $table = 'planting_period';
    protected $fillable = ['form_no', 'pembuatan_lubang_tanam', 'distribution_time', 'distribution_location', 'distribution_coordinates', 'planting_time', 'rec_armada', 'created_at','updated_at', 'start_pembuatan_lubang_tanam', 'end_planting_time'];
}
