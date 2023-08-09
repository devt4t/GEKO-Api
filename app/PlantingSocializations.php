<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PlantingSocializations extends Model
{
    protected $table = 'planting_socializations';
    protected $fillable = ['form_no', 'planting_year', 'farmer_no', 'no_lahan', 'no_document', 'signature', 'ff_no', 'validation', 'validate_by', 'created_at','updated_at','is_dell','max_seed_amount', 'training_material', 'attendance', 'status_approval_nursery', 'reject_description'];
}
