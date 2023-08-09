<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MonitoringDetail extends Model
{
    protected $table = 'monitoring_details';
    protected $fillable = ['monitoring_no', 'tree_code', 'qty','status','condition','planting_date', 'created_at','updated_at'];
}
