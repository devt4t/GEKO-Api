<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WinDetail extends Model
{
    protected $table = 'win_details';
    protected $fillable = ['wins', 'lahan_no', 'tree_code', 'qty_trees',  'created_at', 'updated_at'];
}
