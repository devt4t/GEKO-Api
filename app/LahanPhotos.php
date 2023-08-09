<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LahanPhotos extends Model
{
    protected $table = 'lahan_photos';
    protected $fillable = ['lahan_no', 'filename', 'path', 'created_at', 'updated_at'];
}
