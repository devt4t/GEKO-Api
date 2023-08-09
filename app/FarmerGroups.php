<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FarmerGroups extends Model
{
    protected $table = 'farmer_groups';
    protected $fillable = ['group_no', 'name', 'village', 'mu_no', 'target_area', 'mou_no', 'phone', 'pic', 'organization_structure', 'active', 'user_id','created_at', 'updated_at'];
}
