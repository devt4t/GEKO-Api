<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DistributionDetail extends Model
{
    protected $fillable = [
        'distribution_no', 
        'bag_number', 
        'tree_name', 
        'tree_amount', 
        'tree_category', 
        'detail_date', 
        'is_loaded', 
        'loaded_by', 
        'is_distributed', 
        'distributed_by', 
        'created_at', 
        'updated_at'
    ];
}
