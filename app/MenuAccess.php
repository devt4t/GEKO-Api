<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MenuAccess extends Model
{
    protected $table = 'menu_access';
    protected $fillable = ['parent_code','name',  'path','icon', 'created_at','updated_at'];
}
