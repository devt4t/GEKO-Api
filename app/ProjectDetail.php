<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectDetail extends Model
{
    protected $table = 'project_details';
    protected $fillable = ['project_no', 'lahan_no', 'qty_trees',  'created_at', 'updated_at'];
}
