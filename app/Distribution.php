<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Distribution extends Model
{
    protected $fillable = [
        'distribution_no', 
        'distribution_date', 
        'ff_no', 
        'farmer_no', 
        'farmer_signature', 
        'distribution_note', 
        'distribution_photo', 
        'status', 
        'total_bags', 
        'total_tree_amount', 
        'is_loaded', 
        'loaded_by', 
        'loaded_time', 
        'is_distributed', 
        'distributed_by', 
        'distributed_time', 
        'created_at', 
        'updated_at', 
        'is_dell', 
        'deleted_by',
        'approved_by'
    ];

    public function Maxno()
    {
        
    }
}
