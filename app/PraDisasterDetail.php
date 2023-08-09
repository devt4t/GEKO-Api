<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PraDisasterDetail extends Model
{
    protected $table = 'pra_disaster_details';
    
    protected $fillable = ['pra_no', 'disaster_name', 'disaster_categories', 'year', 'fatalities', 'has_fatalities', 'detail', 'created_at', 'updated_at'];
}