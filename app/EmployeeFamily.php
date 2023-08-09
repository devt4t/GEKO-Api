<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeeFamily extends Model
{
    protected $table = 'employee_families';
    protected $fillable = ['nik', 'detail_name', 'detail_birthplace','detail_birthday', 'detail_address','detail_phone', 'detail_status', 'detail_birthplace', 'created_at', 'updated_at'];
}
