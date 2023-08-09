<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LahanVideos extends Model
{
    protected $table = 'lahan_videos';
    protected $fillable = ['lahan_no', 'filename', 'extension',  'path',  'mime_type', 'created_at', 'updated_at'];
}
