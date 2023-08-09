<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GekoManual extends Model
{
    protected $table = 'geko_manuals';
    protected $fillable = [
        'title', 
        'users', 
        'content',
        'description',
        'created_by',
        'updated_by'
    ];
}
