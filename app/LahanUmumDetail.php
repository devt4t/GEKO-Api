<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LahanUmumDetail extends Model
{
    protected $table = 'lahan_umum_details';
    protected $fillable = ['lahan_no', 'tree_code', 'amount', 'detail_year', 'user_id','created_at', 'updated_at'];
}
