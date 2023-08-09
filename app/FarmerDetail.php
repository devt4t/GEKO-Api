<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FarmerDetail extends Model
{
    protected $table = 'farmer_details';
    protected $fillable = ['farmer_no', 'tree_code', 'amount', 'detail_year', 'user_id','created_at', 'updated_at'];
}
