<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FormMinatFarmer extends Model
{
    protected $table = 'form_minat_farmers';
    protected $fillable = ['form_no', 'name', 'status_program', 'training', 'photo', 'tree1', 'tree2', 'tree3', 'pattern', 'created_at', 'updated_at'];
}
