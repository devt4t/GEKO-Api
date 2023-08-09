<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FarmerPhotos extends Model
{
    protected $table = 'farmer_photos';
    protected $fillable = ['farme_no', 'filename', 'path', 'created_at', 'updated_at'];
}
