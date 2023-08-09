<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PlantingSocializationsDetails extends Model
{
    protected $table = 'planting_details';
    protected $fillable = ['form_no', 'tree_code', 'amount', 'created_at','updated_at'];
}
