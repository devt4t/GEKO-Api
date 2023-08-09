<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SCRSeed extends Model
{
    protected $table = 'seedling_change_request_seeds';
    protected $fillable = [
        'request_no', 
        'lahan_no',
        'type',
        'tree_code',
        'old_amount',
        'new_amount'
    ];
}
