<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LahanUmumHoleDetail extends Model
{
    protected $table = 'lahan_umum_hole_details';
    protected $fillable = ['lahan_no', 'tree_code', 'amount','created_at', 'updated_at'];
}
