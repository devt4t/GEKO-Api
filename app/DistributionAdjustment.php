<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DistributionAdjustment extends Model
{
    protected $fillable = [
        'distribution_no', 
        'ff_no', 
        'farmer_no', 
        'lahan_no', 
        'adjust_date', 
        'tree_code', 
        'tree_category', 
        'total_distributed',
        'broken_seeds',
        'missing_seeds',
        'total_tree_received', 
        'planting_year',
        'is_dell',
        'is_verified',
        'created_by',
        'approved_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];
}