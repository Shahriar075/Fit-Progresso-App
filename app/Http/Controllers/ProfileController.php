<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Models\WorkoutLog;
use App\Models\WorkoutLogExercise;
use App\Services\ExerciseTypeFactory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

class ProfileController extends Controller
{

    public function getWorkoutLogHistory()
    {
        $user = Auth::user();
        $isAdmin = $user->isAdmin();

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

        // Fetch all workout logs for the user (or for all users if admin)
        $query = WorkoutLog::where('user_id', $user->id);

        if ($isAdmin) {
            // If admin, fetch logs for all users
            $query = WorkoutLog::query();
        }

        $workoutLogs = $query->with('workoutLogExercises')->get();

        $formattedLogs = [];
        foreach ($workoutLogs as $workoutLog) {
            // Retrieve total weight and PRs from the workout log
            $totalWeight = $workoutLog->total_weight ?? 0;
            $prCount = $workoutLog->personal_records ?? 0;
            $exerciseDetails = [];

            foreach ($workoutLog->workoutLogExercises as $workoutLogExercise) {
                $exercise = $workoutLogExercise->exercise;

                $exerciseDetails[] = [
                    'exercise_name' => $exercise->name,
                    'best_set' => $workoutLogExercise->best_set,
                ];
            }

            $formattedLogs[] = [
                'workout_log_name' => $workoutLog->name,
                'date' => $workoutLog->workout_date ? \Carbon\Carbon::parse($workoutLog->workout_date)->format('Y-m-d') : 'N/A',
                'duration' => $workoutLog->total_duration ?? 0,
                'total_weight' => "{$totalWeight} kg",
                'personal_records' => "{$prCount} PRs",
                'exercises' => $exerciseDetails,
            ];
        }

        return response()->json($formattedLogs, 201);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

        try {
            $validatedData = $request->validate([
                'name' => 'nullable|string|max:255',
                'email' => 'nullable|string|email|max:255|unique:users,email,' . $user->id,
                'password' => 'nullable|string|confirmed|min:8',
                'role_id' => 'nullable|exists:roles,id',
            ]);
        }catch (\Exception $e){
            return response(['error' => $e->getMessage()], 500);
        }

        if (isset($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        }
        $user->update($validatedData);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }

    public function getExerciseDetails($id)
    {
        $user = Auth::user();

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

        if(!$user){
            return response()->json(['message' => "User not found"], 404);
        }

        $exercise = Exercise::find($id);

        if ($exercise->user_id!=$user->id && !$exercise->isCreatedByAdmin()) {
            return response()->json(['message' => "Unauthorized access"], 403);
        }

        if (!$exercise) {
            return response()->json(['error' => 'Exercise not found'], 404);
        }

        return response()->json(['instructions' => $exercise->instructions ?? 'Instruction not available, as it is not predefined']);
    }

    public function getExerciseHistory($exerciseId)
    {
        $user = Auth::user();

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

        $exercise = Exercise::find($exerciseId);

        if (!$user) {
            return response()->json(['message' => "User not found"], 404);
        }

        if ($exercise->user_id != $user->id && !$exercise->isCreatedByAdmin()) {
            return response()->json(['message' => "Unauthorized access"], 403);
        }

        $workouts = WorkoutLog::where('user_id', $user->id)
            ->whereHas('exercises', function ($query) use ($exerciseId) {
                $query->where('exercise_id', $exerciseId);
            })
            ->with(['exercises' => function ($query) use ($exerciseId) {
                $query->where('exercise_id', $exerciseId)->with(['sets', 'exercise']);
            }])
            ->get();

        $filteredHistory = [];

        foreach ($workouts as $workout) {
            $setsPerformed = [];

            foreach ($workout->exercises as $workoutExercise) {
                $exercise = $workoutExercise->exercise;
                $exerciseType = $exercise->type;

                foreach ($workoutExercise->sets as $set) {
                    if ($exerciseType === 'Strength') {
                        $setsPerformed[] = "{$set->value} kg x {$set->reps} reps";
                    } elseif ($exerciseType === 'Cardio') {
                        $setsPerformed[] = "{$set->value} km | " . gmdate('H:i:s', $set->time_spent);
                    } elseif ($exerciseType === 'Bodyweight') {
                        $setsPerformed[] = "{$set->reps} reps";
                    } elseif ($exerciseType === 'Flexibility') {
                        $setsPerformed[] = "{$set->time_spent} minutes";
                    }
                }
            }

            $filteredHistory[] = [
                'workout_name' => $workout->name,
                'date_time' => $workout->workout_date,
                'sets_performed' => $setsPerformed,
            ];
        }

        if(empty($filteredHistory)){
            return response()->json(['message' => "No exercise history found for that specified exercise"], 404);
        }

        return response()->json($filteredHistory);
    }

    public function getExerciseRecords($exerciseId)
    {
        $user = Auth::user();

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

        if (!$user) {
            return response()->json(['message' => "User not found"], 404);
        }

        $exercise = Exercise::find($exerciseId);
        if (!$exercise) {
            return response()->json(['message' => "Exercise not found"], 404);
        }

        if ($exercise->user_id != $user->id && !$exercise->isCreatedByAdmin()) {
            return response()->json(['message' => "Unauthorized access"], 403);
        }

        $records = WorkoutLogExercise::where('exercise_id', $exerciseId)
            ->whereHas('workoutLog', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with('sets') // Eager load the sets relationship
            ->get();

        $exerciseStats = [];

        foreach ($records as $record) {
            $maxVolume = 0;
            $maxReps = 0;
            $maxWeight = 0;
            $maxTimeSpent = 0;
            $totalReps = 0;
            $totalWeight = 0;
            foreach ($record->sets as $set) {
                $maxVolume = max($maxVolume, $set->value * $set->reps);
                $maxReps = max($maxReps, $set->reps);
                $maxWeight = max($maxWeight, $set->value);
                $maxTimeSpent = max($maxTimeSpent, $set->time_spent);
                $totalReps += $set->reps;
                $totalWeight += $set->value;
            }

            $exerciseStats[] = [
                'workout_log_name' => $record->workoutLog->name,
                'max_volume' => $maxVolume,
                'max_reps' => $maxReps,
                'max_weight' => $maxWeight,
                'max_time_spent' => $maxTimeSpent,
                'total_reps' => $totalReps,
                'total_weight' => $totalWeight,
            ];
        }

        if (empty($exerciseStats)) {
            return response()->json(['message' => 'No records found for the specified exercise'], 404);
        }

        return response()->json($exerciseStats);
    }


    public function searchWorkoutLogsByDate(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->isAdmin();

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $query = WorkoutLog::query();

        if (!$isAdmin) {
            $query->where('user_id', $user->id);
        }

        if ($startDate && $endDate) {
            $endDate = Carbon::parse($endDate)->endOfDay();
            $query->whereBetween('workout_date', [Carbon::parse($startDate)->startOfDay(), $endDate]);
        }

        $workoutLogs = $query->with('workoutLogExercises')->get();

        $formattedLogs = [];

        foreach ($workoutLogs as $workoutLog) {
            $totalWeight = $workoutLog->total_weight ?? 0;
            $prCount = $workoutLog->personal_records ?? 0;

            $exerciseDetails = [];
            foreach ($workoutLog->workoutLogExercises as $workoutLogExercise) {
                $exerciseDetails[] = [
                    'exercise_name' => $workoutLogExercise->exercise->name,
                    'best_set' => $workoutLogExercise->best_set,
                ];
            }

            $formattedLogs[] = [
                'workout_log_name' => $workoutLog->name,
                'date' => $workoutLog->workout_date ? Carbon::parse($workoutLog->workout_date)->format('Y-m-d') : 'N/A',
                'duration' => $workoutLog->total_duration ?? 0,
                'total_weight' => "{$totalWeight} kg",
                'personal_records' => "{$prCount} PRs",
                'exercises' => $exerciseDetails,
            ];
        }

        if (empty($formattedLogs)) {
            return response()->json(['message' => 'No workout records found for the specified exercise in this range'], 404);
        }

        return response()->json($formattedLogs, 200);
    }

}
