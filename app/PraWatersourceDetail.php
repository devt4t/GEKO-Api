<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PraWatersourceDetail extends Model
{
    protected $table = 'pra_watersource_details';
    
    protected $fillable = ['pra_no', 'watersource_name', 'watersource_type', 'watersource_condition', 'consumption', 'watersource_utilization', 'created_at', 'updated_at'];
}