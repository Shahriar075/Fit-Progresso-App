<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkoutLogExercise extends Model
{
    protected $fillable = [
        'workout_log_id', 'exercise_id', 'best_set',
    ];

    public function workoutLog()
    {
        return $this->belongsTo(WorkoutLog::class);
    }

    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }

    public function sets()
    {
        return $this->hasMany(WorkoutLogSet::class);
    }
}
