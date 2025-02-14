<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkoutLogSet extends Model
{
    use HasFactory;

    protected $fillable = ['workout_log_exercise_id', 'set_number', 'value', 'reps', 'time_spent'];

    public function workoutLogExercise()
    {
        return $this->belongsTo(WorkoutLogExercise::class);
    }
}
