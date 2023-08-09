<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PraFarmerIncome extends Model
{
    protected $table = 'pra_farmer_incomes';
    
    protected $fillable = ['pra_no', 'name', 'gender', 'source', 'source_income', 'capacity', 'commodity_name', 'family_member', 'family_type', 'indirect_method', 'job', 'method', 'period', 'created_at', 'updated_at'];
}