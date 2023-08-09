<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RraOrganicPotential extends Model
{
    protected $table = 'rra_organic_potentials';
    
    protected $fillable = ['rra_no', 'potential_category', 'name', 'source', 'description', 'created_at', 'updated_at'];
}