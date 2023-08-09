<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PraExistingProblem extends Model
{
    protected $table = 'pra_existing_problems';
    
    protected $fillable = ['pra_no', 'problem_name', 'problem_categories', 'problem_source', 'problem_solution', 'impact_to_people', 'date', 'interval_problem', 'priority', 'potential', 'total_value', 'ranking', 'created_at', 'updated_at'];
}