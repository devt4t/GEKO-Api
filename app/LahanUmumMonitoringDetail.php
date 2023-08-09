<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LahanUmumMonitoringDetail extends Model
{
    protected $table = 'lahan_umum_monitoring_details';
    protected $fillable = ['monitoring_no', 'tree_code', 'qty','status','condition','planting_date', 'created_at','updated_at'];
}
