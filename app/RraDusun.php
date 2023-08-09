<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RraDusun extends Model
{
    protected $table = 'rra_dusuns';
    
    protected $fillable = ['rra_no', 'dusun_name', 'potential', 'land_area', 'accessibility', 'dry_land_area', 'pic_dusun', 'position', 'phone', 'whatsapp', 'total_rw', 'total_rt', 'total_male', 'total_female', 'total_kk', 'total_farmer_family', 'average_family_member', 'average_farmer_family_member', 'education_elementary_junior_hs', 'education_senior_hs', 'education_college', 'age_productive', 'age_non_productive', 'job_farmer', 'job_farm_workers', 'job_private_employee', 'job_state_employee', 'job_enterpreneur', 'job_others', 'created_at', 'updated_at', 'dusun_access_photo', 'data_land_area_source', 'data_dry_land_area_source', 'has_detail_kk', 'total_non_farmer_family', 'has_avg_member', 'has_detail_avg_member', 'average_non_farmer_family_member', 'data_productive_source', 'data_job_source', 'job_farmer_switcher', 'job_farm_workers_switcher', 'job_private_employee_switcher', 'job_state_employee_switcher', 'job_enterpreneur_switcher', 'job_others_switcher'];
}