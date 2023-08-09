<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RraExistingPlant extends Model
{
    protected $table = 'rra_existing_plants';
    
    protected $fillable = ['rra_no', 'plant_type', 'plant', 'created_at', 'updated_at'];
}