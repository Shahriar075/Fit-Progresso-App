<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Services\ExerciseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExerciseController extends Controller
{
    protected $exerciseService;

    public function __construct(ExerciseService $exerciseService)
    {
        $this->exerciseService = $exerciseService;
    }

    public function index()
    {
        $exercises = $this->exerciseService->getExercises();
        return response()->json($exercises);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:exercises,name',
            'type' => 'required|string|in:' . implode(',', Exercise::$types),
            'description' => 'nullable|string',
        ]);

        $exercise = $this->exerciseService->createExercise($validatedData);
        return response()->json(['message' => "Exercise created successfully", 'exercise' => $exercise], 201);
    }

    public function update(Request $request, Exercise $exercise)
    {
        $validatedData = $request->validate([
            'name' => 'string|max:255',
            'type' => 'string|in:' . implode(',', Exercise::$types),
            'description' => 'nullable|string',
        ]);

        $exercise = $this->exerciseService->updateExercise($exercise, $validatedData);
        return response()->json(['message' => "Exercise updated successfully", 'exercise' => $exercise], 200);
    }

    public function destroy(Exercise $exercise)
    {
        $this->exerciseService->deleteExercise($exercise);
        return response()->json(['message' => 'Exercise deleted successfully']);
    }
}
