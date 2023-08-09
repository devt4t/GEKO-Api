<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ScoopingVisitFigure extends Model
{
    protected $table = 'scooping_visit_figures';
    
    protected $fillable = ['data_no', 'name', 'position', 'phone', 'whatsapp', 'created_at', 'updated_at'];
}