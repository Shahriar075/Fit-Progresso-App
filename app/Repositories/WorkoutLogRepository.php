<?php

namespace App\Repositories;

use App\Models\WorkoutLog;
use App\Models\WorkoutLogExercise;
use App\Models\WorkoutLogSet;

class WorkoutLogRepository
{
    public function getAllWorkoutLogs()
    {
        return WorkoutLog::with('exercises.sets')->get();
    }
    
    public function getUserWorkoutLogs($userId)
    {
        return WorkoutLog::where('user_id', $userId)
            ->with('exercises.sets')
            ->get();
    }

    public function createWorkoutLog(array $data)
    {
        return WorkoutLog::create($data);
    }

    public function updateWorkoutLog(WorkoutLog $workoutLog, array $data)
    {
        return $workoutLog->update($data);
    }

    public function deleteWorkoutLog(WorkoutLog $workoutLog)
    {
        return $workoutLog->delete();
    }

    public function createWorkoutLogExercise(array $data)
    {
        return WorkoutLogExercise::create($data);
    }

    public function createWorkoutLogSet(array $data)
    {
        return WorkoutLogSet::create($data);
    }

    public function deleteWorkoutLogExercises(WorkoutLog $workoutLog)
    {
        $exerciseIds = $workoutLog->exercises->pluck('id');
        
        WorkoutLogSet::whereIn('workout_log_exercise_id', $exerciseIds)->delete();

        return $workoutLog->exercises()->delete();
    }
}