<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeeStructure extends Model
{
    protected $table = 'employee_structure';
    protected $fillable = ['nik', 'manager_code', 'menu_access','created_at','updated_at'];
}
