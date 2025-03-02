<?php

namespace App\Http\Controllers;

use App\Models\WorkoutLog;
use App\Services\WorkoutLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkoutLogController extends Controller
{
    protected $workoutLogService;

    public function __construct(WorkoutLogService $workoutLogService)
    {
        $this->workoutLogService = $workoutLogService;
    }

    public function index()
    {
        $user = auth()->user();

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

        $result = $this->workoutLogService->getAllWorkoutLogs($user);

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 404);
        }

        return response()->json($result);
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

        $result = $this->workoutLogService->createWorkoutLog($validated, $user);

        if (isset($result['errors'])) {
            return response()->json(['errors' => $result['errors']], 422);
        }

        return response()->json($result, 201);
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

        $result = $this->workoutLogService->updateWorkoutLog($validated, $workoutLog, $user);

        if (isset($result['errors'])) {
            return response()->json(['errors' => $result['errors']], 422);
        }

        return response()->json($result, 201);
    }

    public function destroy(WorkoutLog $workoutLog)
    {
        $user = Auth::user();

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

        if (!$workoutLog->exists) {
            return response()->json(['error' => "Workout Log Not Found"], 404);
        }

        $result = $this->workoutLogService->deleteWorkoutLog($workoutLog, $user);

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 403);
        }

        return response()->json(['message' => 'Workout log deleted']);
    }
}