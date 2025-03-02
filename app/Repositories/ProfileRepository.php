<?php

namespace App\Repositories;

use App\Models\Exercise;
use App\Models\User;
use App\Models\WorkoutLog;
use App\Models\WorkoutLogExercise;
use Carbon\Carbon;

class ProfileRepository
{
    public function getWorkoutLogs(User $user, bool $isAdmin)
    {
        $query = WorkoutLog::where('user_id', $user->id);

        if ($isAdmin) {
            $query = WorkoutLog::query();
        }

        return $query->with('workoutLogExercises')->get();
    }

    public function updateUser(User $user, array $data)
    {
        $user->update($data);
        return $user;
    }

    public function findExerciseById($exerciseId)
    {
        return Exercise::find($exerciseId);
    }

    public function getExerciseWorkouts($userId, $exerciseId)
    {
        return WorkoutLog::where('user_id', $userId)
            ->whereHas('exercises', function ($query) use ($exerciseId) {
                $query->where('exercise_id', $exerciseId);
            })
            ->with(['exercises' => function ($query) use ($exerciseId) {
                $query->where('exercise_id', $exerciseId)->with(['sets', 'exercise']);
            }])
            ->get();
    }

    public function getExerciseRecords($exerciseId, $userId)
    {
        return WorkoutLogExercise::where('exercise_id', $exerciseId)
            ->whereHas('workoutLog', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->with(['sets', 'workoutLog'])
            ->get();
    }

    public function searchWorkoutLogs(User $user, bool $isAdmin, $startDate, $endDate)
    {
        $query = WorkoutLog::query();

        if (!$isAdmin) {
            $query->where('user_id', $user->id);
        }

        if ($startDate && $endDate) {
            $parsedEndDate = Carbon::parse($endDate)->endOfDay();
            $query->whereBetween('workout_date', [
                Carbon::parse($startDate)->startOfDay(),
                $parsedEndDate
            ]);
        }

        return $query->with('workoutLogExercises')->get();
    }
}