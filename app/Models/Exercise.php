<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exercise extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'type', 'description', 'user_id', 'instructions'
    ];

    public static $types = [
        'Strength',
        'Legs',
        'Cardio',
        'Flexibility',
        'Balance',
        'Endurance',
        'Bodyweight',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function workoutTemplates()
    {
        return $this->belongsToMany(WorkoutTemplate::class, 'exercise_workout_template')
            ->withPivot('sequence');
    }

    public function sets()
    {
        return $this->belongsToMany(WorkoutLogSet::class);
    }

    public function workoutLogExercises()
    {
        return $this->hasMany(WorkoutLogExercise::class);
    }

    public function isCreatedByAdmin()
    {
        return $this->user_id === null || User::find($this->user_id)->isAdmin();
    }
}
