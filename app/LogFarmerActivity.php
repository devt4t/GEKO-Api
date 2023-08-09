<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LogFarmerActivity extends Model
{
    protected $table = 'log_farmer_activities';
    protected $fillable = ['log_time', 'status_type', 'activity',  'farmer_no',  'name', 'no_ktp', 'fc', 'email_log', 'created_at', 'updated_at'];
}