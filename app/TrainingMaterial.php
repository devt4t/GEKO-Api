<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrainingMaterial extends Model
{
    protected $table = 'training_materials';

    protected $fillable = ['material_no', 'material_name', 'created_at', 'updated_at'];
}
