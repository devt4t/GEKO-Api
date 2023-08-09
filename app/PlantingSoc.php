<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PlantingSoc extends Model
{
    protected $table = 'planting_socs';
    protected $fillable = ['soc_no', 'program_year', 'ff_no', 'soc_date', 'absent'];
}