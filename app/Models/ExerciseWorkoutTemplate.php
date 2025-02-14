<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExerciseWorkoutTemplate extends Model
{
    use HasFactory;

    protected $fillable = ['workout_template_id', 'exercise_id'];
}
