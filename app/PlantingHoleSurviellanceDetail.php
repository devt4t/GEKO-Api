<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PlantingHoleSurviellanceDetail extends Model
{
    protected $table = 'planting_hole_details';
    protected $fillable = ['ph_form_no', 'tree_code', 'amount', 'created_at','updated_at'];
}
