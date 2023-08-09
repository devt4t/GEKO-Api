<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Trees extends Model
{
    protected $table = 'trees';
    protected $fillable = ['tree_code','tree_name', 'scientific_name','english_name', 'common_name','short_information', 
    'description','tree_category', 'product_list','estimate_income', 'co2_capture','photo1', 'photo2', 'created_at','updated_at'];
}
