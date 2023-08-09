<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FarmerTrainingDetail extends Model
{
    protected $table = 'farmer_training_details';

    protected $fillable = ['training_no', 'training_date', 'farmer_no', 'created_at', 'updated_at'];
}
