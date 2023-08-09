<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LahanUmum extends Model
{
    protected $table = 'lahan_umums';
    protected $fillable = [
        'lahan_no', 
        'mu_no',
        'province', 
        'regency', 
        'district', 
        'village', 
        'employee_no', 
        'pic_lahan', 
        'ktp_no', 
        'address', 
        'mou_no', 
        'program_year', 
        'luas_lahan', 
        'luas_tanam', 
        'pattern_planting', 
        'access_lahan', 
        'jarak_lahan', 
        'status', 
        'longitude', 
        'latitude', 
        'distribution_date', 
        'planting_hole_date',
        'planting_realization_date', 
        'complete_data',
        'is_verified', 
        'verified_by', 
        'created_at', 
        'updated_at', 
        'photo1', 
        'photo2', 
        'photo3', 
        'photo_doc', 
        'active', 
        'coordinate', 
        'tutupan_lahan', 
        'is_dell', 
        'description',
        'created_by',
        'is_checked',
        'checked_by'
    ];
}
