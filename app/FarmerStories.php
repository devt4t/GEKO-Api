<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FarmerStories extends Model
{
    protected $table = 'farmer_stories';
    protected $fillable = ['farme_no', 'story_no', 'story_title', 'year', 'story_description', 'active', 'created_at', 'updated_at'];
}
