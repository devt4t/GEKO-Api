<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LahanDetail extends Model
{
    protected $table = 'lahan_details';
    protected $fillable = ['lahan_no', 'tree_code', 'amount', 'detail_year', 'user_id','created_at', 'updated_at'];
}
