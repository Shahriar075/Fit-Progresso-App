<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Models\WorkoutLog;
use App\Models\WorkoutLogExercise;
use App\Models\WorkoutLogSet;
use App\Services\BodyweightExercise;
use App\Services\CardioExercise;
use App\Services\ExerciseType;
use App\Services\ExerciseTypeFactory;
use App\Services\FlexibilityExercise;
use App\Services\StrengthExercise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkoutLogController extends Controller
{
    protected $exerciseTypeFactory;

    public function __construct(ExerciseTypeFactory $exerciseTypeFactory)
    {
        $this->exerciseTypeFactory = $exerciseTypeFactory;
    }

    public function index()
    {
        $user = auth()->user();

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

        if ($user->isAdmin()) {
            $workoutLogs = WorkoutLog::with('exercises.sets')->get();
        } else {
            $workoutLogs = WorkoutLog::where('user_id', $user->id)->with('exercises.sets')->get();
        }

        if ($workoutLogs->isEmpty()) {
            return response()->json(['error' => 'No workout logs found.'], 404);
        }

        return response()->json($workoutLogs);
    }

    public function create(Request $request)
    {

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'total_duration' => 'nullable|integer',
                'workout_date' => 'required|date',
                'exercises' => 'nullable|array',
                'exercises.*.exercise_id' => 'required|exists:exercises,id',
                'exercises.*.sets' => 'nullable|array',
                'exercises.*.sets.*.set_number' => 'nullable|integer',
                'exercises.*.sets.*.value' => 'nullable|numeric',
                'exercises.*.sets.*.reps' => 'nullable|integer',
                'exercises.*.sets.*.time_spent' => 'nullable|integer',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid request format.'], 422);
        }

        $user = Auth::user();

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

        $totalWeight = 0;
        $index = -1;
        $prCount = 0;
        $exerciseDetails = [];
        $errors = [];
        $setStore = [];

        // Validate both the exercises and sets
        if (isset($validated['exercises'])) {
            foreach ($validated['exercises'] as $exerciseData) {
                $exercise = Exercise::find($exerciseData['exercise_id']);
                $exerciseType = $exercise->type;

                // Check if exercise belongs to user or if it is created by the admin
                if ($exercise->user_id !== $user->id && !$exercise->isCreatedByAdmin()) {
                    $errors[] = 'You do not have permission to use exercise ID ' . $exerciseData['exercise_id'];
                    continue;
                }

                // Get the appropriate exercise type instance
                $exerciseTypeInstance = ExerciseTypeFactory::create($exerciseType);

                // Validate exercise sets based on type
                try {
                    $bestSet = $exerciseTypeInstance->getBestSet($exerciseData['sets']);
                    $setStore[] = $bestSet;
                    if (is_string($bestSet) && strpos($bestSet, 'error') !== false) {
                        $errors[] = $bestSet;
                    }
                } catch (\InvalidArgumentException $e) {
                    $errors[] = $e->getMessage();
                }

                // Collect exercise details for valid exercises
                if (empty($errors)) {
                    $exerciseDetails[] = [
                        'exercise_name' => $exercise->name,
                        'best_set' => $bestSet ?? null,
                    ];

                    $sets = [];
                    // Calculate total weight and update personal records
                    foreach ($exerciseData['sets'] as $setData) {
                        $weight = $setData['value'] ?? 0;
                        $reps = $setData['reps'] ?? 0;

                        // Update total weight based on exercise type
                        if ($exerciseType === 'Cardio' || $exerciseType === 'Bodyweight' || $exerciseType === 'Flexibility') {
                            $weight = 0;
                        }
                        $totalWeight += $weight * $reps;
                        $sets[] = $setData;
                    }
                        // Update personal records and count PRs
                        if ($exerciseTypeInstance->updatePersonalRecord($user->id, $exercise->id, $sets)) {
                            $prCount++;
                    }
                }
            }
        }

        if (!empty($errors)) {
            return response()->json(['errors' => $errors], 422);
        }

        $workoutLog = WorkoutLog::create([
            'name' => $validated['name'],
            'total_duration' => $validated['total_duration'],
            'workout_date' => $validated['workout_date'],
            'user_id' => $user->id,
            'total_weight' => $totalWeight,
            'personal_records' => $prCount,
        ]);

        // Attach exercises and sets to the workout log
        foreach ($validated['exercises'] as $exerciseData) {
            $index++;
            $exercise = Exercise::find($exerciseData['exercise_id']);

            $workoutLogExercise = WorkoutLogExercise::create([
                'workout_log_id' => $workoutLog->id,
                'exercise_id' => $exercise->id,
                'best_set' => $setStore[$index],
            ]);

            // Attach the input sets
            foreach ($exerciseData['sets'] as $setData) {
                WorkoutLogSet::create([
                    'workout_log_exercise_id' => $workoutLogExercise->id,
                    'set_number' => $setData['set_number'],
                    'value' => $setData['value'] ?? 0,
                    'reps' => $setData['reps'] ?? 0,
                    'time_spent' => $setData['time_spent'] ?? null,
                ]);
            }
        }

        // Return response with the updated information
        $response = [
            'message' => 'Congratulations!!!!',
            'workout_log_name' => $validated['name'],
            'date' => $validated['workout_date'],
            'duration' => $validated['total_duration'] ?? 0,
            'total_weight' => "{$totalWeight} kg",
            'personal_records' => "{$prCount} PRs",
            'exercises' => $exerciseDetails,
        ];

        return response()->json($response, 201);
    }

    public function update(Request $request, WorkoutLog $workoutLog)
    {
        $user = Auth::user();

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'total_duration' => 'nullable|integer',
                'workout_date' => 'required|date',
                'exercises' => 'nullable|array',
                'exercises.*.exercise_id' => 'required|exists:exercises,id',
                'exercises.*.sets' => 'nullable|array',
                'exercises.*.sets.*.set_number' => 'nullable|integer',
                'exercises.*.sets.*.value' => 'nullable|numeric',
                'exercises.*.sets.*.reps' => 'nullable|integer',
                'exercises.*.sets.*.time_spent' => 'nullable|integer',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid request format.'], 422);
        }

        // Check if the workout log belongs to the user
        if ($workoutLog->user_id !== $user->id) {
            return response()->json(['error' => 'You do not have permission to update this workout log.'], 403);
        }

        $totalWeight = 0;
        $prCount = 0;
        $exerciseDetails = [];
        $errors = [];
        $setStore = [];

        // Validate both the exercises and sets
        if (isset($validated['exercises'])) {
            foreach ($validated['exercises'] as $index => $exerciseData) {
                $exercise = Exercise::find($exerciseData['exercise_id']);
                $exerciseType = $exercise->type;

                // Check if exercise belongs to user or if it is created by the admin
                if ($exercise->user_id !== $user->id && !$exercise->isCreatedByAdmin()) {
                    $errors[] = 'You do not have permission to use exercise ID ' . $exerciseData['exercise_id'];
                    continue;
                }

                // Get the appropriate exercise type instance
                $exerciseTypeInstance = ExerciseTypeFactory::create($exerciseType);

                // Validate exercise sets based on type
                try {
                    $bestSet = $exerciseTypeInstance->getBestSet($exerciseData['sets']);
                    $setStore[$index] = $bestSet;
                    if (is_string($bestSet) && strpos($bestSet, 'error') !== false) {
                        $errors[] = $bestSet;
                    }
                } catch (\InvalidArgumentException $e) {
                    $errors[] = $e->getMessage();
                }

                if (empty($errors)) {
                    $exerciseDetails[] = [
                        'exercise_name' => $exercise->name,
                        'best_set' => $bestSet ?? null,
                    ];

                    $sets = [];
                    // Calculate total weight and prepare WorkoutLogSet entries
                    foreach ($exerciseData['sets'] as $setData) {
                        $weight = $setData['value'] ?? 0;
                        $reps = $setData['reps'] ?? 0;

                        // Update total weight based on exercise type
                        if ($exerciseType === 'Cardio' || $exerciseType === 'Bodyweight' || $exerciseType === 'Flexibility') {
                            $weight = 0;
                        }
                        $totalWeight += $weight * $reps;
                        $sets[] = $setData;
                    }

                    // Update personal records and count PRs
                    if ($exerciseTypeInstance->updatePersonalRecord($user->id, $exercise->id, $sets)) {
                        $prCount++;
                    }
                }
            }
        }

        if (!empty($errors)) {
            return response()->json(['errors' => $errors], 422);
        }

        $workoutLog->update([
            'name' => $validated['name'],
            'total_duration' => $validated['total_duration'],
            'workout_date' => $validated['workout_date'],
            'total_weight' => $totalWeight,
            'personal_records' => $prCount,
        ]);

        // Remove the existing exercises and sets attached with the workout log
        $workoutLog->exercises()->delete();
        $exerciseIds = $workoutLog->exercises->pluck('id');
        WorkoutLogSet::whereIn('workout_log_exercise_id', $exerciseIds)->delete();

        // Attach the updated exercises and sets to the workout log
        foreach ($validated['exercises'] as $index => $exerciseData) {
            $exercise = Exercise::find($exerciseData['exercise_id']);

            $workoutLogExercise = WorkoutLogExercise::create([
                'workout_log_id' => $workoutLog->id,
                'exercise_id' => $exercise->id,
                'best_set' => $setStore[$index],
            ]);

            foreach ($exerciseData['sets'] as $setData) {
                WorkoutLogSet::create([
                    'workout_log_exercise_id' => $workoutLogExercise->id,
                    'set_number' => $setData['set_number'],
                    'value' => $setData['value'] ?? 0,
                    'reps' => $setData['reps'] ?? 0,
                    'time_spent' => $setData['time_spent'] ?? null,
                ]);
            }
        }

        // Return response with the updated information
        $response = [
            'message' => 'Congratulations!!!!',
            'workout_log_name' => $validated['name'],
            'date' => $validated['workout_date'],
            'duration' => $validated['total_duration'] ?? 0,
            'total_weight' => "{$totalWeight} kg",
            'personal_records' => "{$prCount} PRs",
            'exercises' => $exerciseDetails,
        ];

        return response()->json($response, 201);
    }

    public function destroy(WorkoutLog $workoutLog)
    {
        $user = Auth::user();

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

        if(!$workoutLog->exists){
            return response()->json(['error' => "Workout Log Not Found"], 404);
        }

        if ($workoutLog->user_id === $user->id) {
            $workoutLog->delete();
            return response()->json(['message' => 'Workout log deleted']);
        } else {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    }
}
