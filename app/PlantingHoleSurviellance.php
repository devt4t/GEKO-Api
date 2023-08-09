<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PlantingHoleSurviellance extends Model
{
    protected $table = 'planting_hole_surviellance';
    protected $fillable = ['ph_form_no', 'planting_year', 'lahan_no', 'latitude', 'longitude', 'total_holes', 'counter_hole_standard',
    'farmer_signature', 'gambar1', 'gambar2', 'gambar3', 'pohon_kayu','pohon_mpts','tanaman_bawah','user_id',
    'is_validate', 'validate_by', 'created_at','updated_at','is_dell', 'status_approval_nursery', 'reject_description'];
}
