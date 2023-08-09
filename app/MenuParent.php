<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MenuParent extends Model
{
    protected $table = 'menu_access_parent';
    protected $fillable = ['name', 'icon', 'created_at','updated_at'];
}
