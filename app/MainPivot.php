<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MainPivot extends Model
{
    protected $table = 'main_pivots';
    protected $fillable = ['type', 'key1', 'key2','active','program_year', 'created_at','updated_at'];
}