<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhoneBaseline extends Model
{
    protected $fillable = [
        'phone_number',
        'latitude',
        'longitude',
    ];
}
