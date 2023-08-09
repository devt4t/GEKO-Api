<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SocialimpactFloraDetail extends Model
{
    protected $table = 'socialimpact_flora_details';
    
    protected $fillable = ['pra_no', 'flora_name', 'flora_categories', 'flora_population', 'flora_foodsource', 'flora_status', 'flora_habitat', 'created_at', 'updated_at'];
}