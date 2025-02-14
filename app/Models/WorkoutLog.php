<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkoutLog extends Model
{
    protected $fillable = [
        'name', 'total_duration', 'workout_date', 'user_id', 'total_weight', 'personal_records'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function exercises()
    {
        return $this->hasMany(WorkoutLogExercise::class);
    }

    public function workoutLogExercises()
    {
        return $this->hasMany(WorkoutLogExercise::class);
    }
}
