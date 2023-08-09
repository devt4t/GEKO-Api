<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FieldFacilitator extends Model
{
    protected $table = 'field_facilitators';
    protected $fillable = ['ff_no','name', 'birthday','religion', 'gender','marrital', 'join_date','ktp_no',  
    'phone','address', 'village','kecamatan', 'city','province', 'post_code', 'working_area', 'mu_no','target_area', 'bank_account', 
    'bank_branch','bank_name','ff_photo','ff_photo_path','active','user_id','created_at','updated_at', 'fc_no'];
}
