<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SCR extends Model
{
    protected $table = 'seedling_change_requests';
    protected $fillable = [
        'request_no', 
        'program_year', 
        'land_program',
        'distribution_date',
        'nursery',
        'mu_no',
        'mou_no',
        'farmer_no',
        'lahan_no',
        'notes',
        'last_activity',
        'changed_datas',
        'status',
        'verification',
        'created_by',
        'verification1_by',
        'verification2_by',
        'verification3_by',
        'execute_time'];
}
