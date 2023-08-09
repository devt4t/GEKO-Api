<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PraFertilizerDetail extends Model
{
    protected $table = 'pra_fertilizer_details';
    
    protected $fillable = ['pra_no', 'fertilizer_name', 'fertilizer_categories', 'fertilizer_type', 'fertilizer_source', 'fertilizer_description', 'created_at', 'updated_at'];
}