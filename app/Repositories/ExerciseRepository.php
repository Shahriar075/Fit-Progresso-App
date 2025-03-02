<?php

namespace App\Repositories;

use App\Models\Exercise;

class ExerciseRepository
{
    public function getAllExercises()
    {
        return Exercise::all();
    }

    public function getUserExercises($user)
    {
        return Exercise::where('user_id', $user->id)
            ->orWhereHas('user', function ($query) {
                $query->where('role_id', 1);
            })
            ->get();
    }

    public function createExercise($data)
    {
        return Exercise::create($data);
    }

    public function findExercise($id)
    {
        return Exercise::findOrFail($id);
    }

    public function updateExercise($exercise, $data)
    {
        $exercise->update($data);
        return $exercise;
    }

    public function deleteExercise($exercise)
    {
        $exercise->delete();
    }
}
