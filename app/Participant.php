<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    protected $table = 'participants';
    protected $fillable = ['participant_no', 'participant_category', 'first_name', 'last_name', 'address1', 'address2', 'company', 'city', 
    'state', 'postal_code', 'country', 'email', 'website', 'phone', 'join_date', 'active', 'user_id', 'photo', 'comment', 
    'source_of_contact','created_at', 'updated_at'];
}
