<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RraCommunityInstitution extends Model
{
    protected $table = 'rra_community_institutions';
    
    protected $fillable = ['rra_no', 'institution_name', 'role', 'description', 'created_at', 'updated_at'];
}