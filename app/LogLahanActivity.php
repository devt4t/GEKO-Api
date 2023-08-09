<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LogLahanActivity extends Model
{
    protected $table = 'log_lahan_activities';
    protected $fillable = ['log_time', 'status_type', 'activity', 'lahan_no', 'farmer_no',  'name', 'ff_no', 'ff_name', 'fc', 'updated_log', 'email_log', 'created_at', 'updated_at'];
}