<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMeasure extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'measure_type_id', 'value', 'recorded_on'
    ];
}
