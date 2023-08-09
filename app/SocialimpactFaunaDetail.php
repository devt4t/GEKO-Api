<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SocialimpactFaunaDetail extends Model
{
    protected $table = 'socialimpact_fauna_details';
    
    protected $fillable = ['pra_no', 'fauna_name', 'fauna_categories', 'fauna_population', 'fauna_foodsource', 'fauna_status', 'fauna_habitat', 'created_at', 'updated_at'];
}