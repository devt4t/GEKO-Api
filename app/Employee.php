<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = 'employees';
    protected $fillable = ['nik', 'parent_no', 'name', 'alias', 'ktp_no', 'kk_no', 'email', 'address', 
    'city', 'kelurahan', 'kecamatan', 'province', 'birthday', 'birthplace', 'phone', 'marrital', 'blood_type', 'religion', 'zipcode', 
    'gender','npwp','bank_account', 'bank_branch', 'bank_name', 'job_status', 'job_start', 'job_end', 'bpjs_kesehatan_no', 
    'bpjs_tenagakerja_no', 'position_no', 'mother_name', 'employee_photo','is_user', 'created_at', 'updated_at'];
}
