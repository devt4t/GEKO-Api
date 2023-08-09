<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Win extends Model
{
    protected $table = 'wins';
    protected $fillable = ['wins', 'order_no', 'bl_no', 'shipment_no', 'participant_no', 'retailer_no', 'longitude', 'latitude', 
    'user_id', 'total_trees', 'verified_by','is_verified','project_no', 'created_at', 'updated_at'];
}
