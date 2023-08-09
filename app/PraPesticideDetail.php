<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PraPesticideDetail extends Model
{
    protected $table = 'pra_pesticide_details';
    
    protected $fillable = ['pra_no', 'pesticide_name', 'pesticide_categories', 'pesticide_type', 'pesticide_source', 'pesticide_description', 'created_at', 'updated_at'];
}