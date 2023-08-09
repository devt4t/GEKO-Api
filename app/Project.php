<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = 'projects';
    protected $fillable = ['project_no', 'project_category', 'project_name', 'project_date', 'end_project', 'project_description', 'location', 'total_trees', 
    'co2_capture', 'donors', 'mu_list', 'created_at', 'updated_at'];
}
