<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RraProductionMarketing extends Model
{
    protected $table = 'rra_production_marketings';
    
    protected $fillable = ['rra_no', 'commodity_name', 'capacity', 'method', 'period', 'description', 'customer', 'phone', 'email', 'created_at', 'updated_at', 'capacity_switcher', 'has_customer'];
}