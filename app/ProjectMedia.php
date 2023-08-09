<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectMedia extends Model
{
    protected $table = 'project_medias';
    protected $fillable = ['project_no', 'filename', 'media_type',  'created_at', 'updated_at'];
}
