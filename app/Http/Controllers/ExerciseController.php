<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExerciseController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if($user->isAdmin()){
            $exercises = Exercise::all();
        }
        else {
            $exercises = Exercise::where('user_id', $user->id)
                ->orWhereHas('user', function ($query) {
                    $query->where('role_id', 1);
                })
            ->get();
        }

        return response()->json($exercises);
    }

    public function store(Request $request)
    {
        try {
            $rules = $request->validate([
                'name' => 'required|string|max:255|unique:exercises,name',
                'type' => 'required|string|in:' . implode(',', Exercise::$types),
                'description' => 'nullable|string',
            ]);
        } catch (\Exception $e)
        {
              return response()->json(['message' => $e->getMessage()], 500);
        }

        $user = Auth::user();

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

        $exercise = new Exercise();
        $exercise->name = $rules['name'];
        $exercise->type = $rules['type'];
        $exercise->description = $rules['description'];
        $exercise->user_id = $user->id;
        $exercise->save();

        return response()->json([
            'message' => "Exercise created successfully",
            'exercise' => $exercise
        ], 201);
    }

    public function update(Request $request, Exercise $exercise)
    {
        try {
            $rules = $request->validate([
                'name' => 'string|max:255',
                'type' => 'string|in:' . implode(',', Exercise::$types),
                'description' => 'nullable|string',
            ]);
        } catch (\Exception $e)
        {
            return response()->json(['message' => $e->getMessage()], 500);
        }

        $user = Auth::user();

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

        if ($exercise->user_id == $user->id) {
            $exercise->name = $rules['name'];
            $exercise->type = $rules['type'];
            $exercise->description = $rules['description'];
            $exercise->save();

            return response()->json([
                'message' => "Exercise updated successfully",
                'exercise' => $exercise
            ], 200);
        }

        return response()->json(['error' => 'Unauthorized'], 403);
    }

    public function destroy(Exercise $exercise)
    {
        $user = Auth::user();

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

        if ($exercise->user_id === $user->id) {
            $exercise->delete();

            return response()->json(['message' => 'Exercise deleted successfully']);
        }

        return response()->json(['error' => 'Unauthorized'], 403);
    }

    public function createPredefinedExercise(Request $request)
    {
        $user = Auth::user();

        if(!$user || $user!=$user->isAdmin())
        {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $rules = $request->validate([
                'name' => 'required|string|max:255|unique:exercises,name',
                'type' => 'required|string|in:' . implode(',', Exercise::$types),
                'description' => 'nullable|string',
                'instructions' => 'nullable|string',
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }

        $exercise = new Exercise();
        $exercise->name = $rules['name'];
        $exercise->type = $rules['type'];
        $exercise->description = $rules['description'];
        $exercise['instructions'] = $rules['instructions'];
        $exercise->user_id = $user->id;

        $exercise->save();

        return response()->json($exercise, 201);
    }

    public function searchExercise(Request $request)
    {
        $user = Auth::user();

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Validate the search query
        $validatedName = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $name = $validatedName['name'];

        $query = Exercise::where('name', 'LIKE', "%$name%");

        // Apply user role restrictions
        if (!$user->isAdmin()) {
            $query->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhereHas('user', function ($query) {
                        $query->where('role_id', 1);
                    });
            });
        }

        $exercises = $query->get();

        if(count($exercises) > 0) {
            return response()->json($exercises);
        }

        return response()->json(['error' => 'Exercises not found'], 404);
    }


}
