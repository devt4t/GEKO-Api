<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeePosition extends Model
{
    protected $table = 'employee_positions';
    protected $fillable = ['position_no', 'name', 'position_group', 'created_at', 'updated_at'];
}
