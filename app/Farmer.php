<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Farmer extends Model
{
    protected $table = 'farmers';
    protected $fillable = ['farmer_no', 'name', 'nickname', 'birthday', 'religion', 'ethnic', 'origin', 'gender', 'join_date', 'number_family_member', 'ktp_no', 'phone','rt','rw', 'address', 'village',
    'kecamatan', 'city', 'province', 'post_code', 'mu_no', 'target_area', 'group_no', 'mou_no', 'main_income', 'side_income', 'active', 'user_id', 'created_at', 'updated_at', 'ktp_document',
    'farmer_profile', 'marrital_status', 'main_job', 'side_job', 'education', 'non_formal_education', 'signature','is_dell','complete_data'];
}
