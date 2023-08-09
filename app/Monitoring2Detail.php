<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Monitoring2Detail extends Model
{
    protected $table = 'monitoring_2_detail';
    protected $fillable = ['monitoring_no', 'tree_code', 'qty','status','condition','planting_date', 'created_at','updated_at'];
}
