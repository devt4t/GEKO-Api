<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $table = 'activities';
    protected $fillable = ['activity_no','activity_type', 'activity_date','field_facilitator', 'lahan_no','total_trees',
    'programs','activity_description','user_id','verification_code','created_at','updated_at'];
}
