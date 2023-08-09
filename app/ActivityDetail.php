<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActivityDetail extends Model
{
    protected $table = 'activity_details';
    protected $fillable = ['activity_no','tree_code', 'amount','detail_date', 'tree_status','growth_percentage',
    'diameter','high','polybags','list_of_things','created_at','updated_at'];
}
