<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FarmerTestimonial extends Model
{
    protected $table = 'farmer_testimonial';
    protected $fillable = ['farme_no', 'testimonial', 'year', 'created_at', 'updated_at'];
}
