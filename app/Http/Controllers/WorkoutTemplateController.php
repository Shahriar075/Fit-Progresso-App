<?php

namespace App\Http\Controllers;

use App\Models\WorkoutTemplate;
use App\Models\Exercise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkoutTemplateController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

        if ($user->isAdmin()) {
            $templates = WorkoutTemplate::with('exercises')->get();
        } else {
            $templates = WorkoutTemplate::where('created_by', $user->id)
                ->orWhereHas('user', function($query) {
                    $query->where('role_id', 1);
                })
                ->with('exercises')
                ->get();
        }

        if($templates->isEmpty()){
            return response()->json(['error' => 'No templates found.'], 404);
        }
        return response()->json($templates);
    }
    public function create(Request $request)
    {
        $user = Auth::user();

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'exercise_ids' => 'required|array',
                'exercise_ids.*' => 'exists:exercises,id',
            ]);
        } catch (\Exception $e){
            return response()->json(['error' => $e->getMessage()], 422);
        }

        // Check if the user is authorized to use the exercises
        $authorizedExerciseIds = Exercise::whereIn('id', $request->exercise_ids)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhereHas('user', function($query) {
                        $query->where('role_id', 1); // Admin role ID
                    });
            })
            ->pluck('id')
            ->toArray();

        if (count($authorizedExerciseIds) !== count($request->exercise_ids)) {
            return response()->json(['error' => 'Unauthorized exercise inclusion'], 403);
        }

        $template = new WorkoutTemplate();
        $template->name = $request->name;
        $template->created_by = $user->id;
        $template->description = $request->description;
        $template->save();

        $template->exercises()->attach($authorizedExerciseIds);

        return response()->json(['message' => 'Workout template created successfully', 'template' => $template]);
    }

    public function show(WorkoutTemplate $template)
    {
        $user = Auth::user();

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

        if ($user->isAdmin() || $template->user_id == $user->id) {
            return response()->json($template->load('exercises'));
        }

        return response()->json(['error' => 'Unauthorized'], 403);
    }

    public function update(Request $request, WorkoutTemplate $template)
    {
        $user = Auth::user();

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

        if ($template->created_by != $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'exercise_ids' => 'required|array',
                'exercise_ids.*' => 'exists:exercises,id',
            ]);
        } catch (\Exception $e){
            return response()->json(['error' => $e->getMessage()], 422);
        }

        // Check if the user is authorized to use the exercises
        $authorizedExerciseIds = Exercise::whereIn('id', $request->exercise_ids)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhereHas('user', function($query) {
                        $query->where('role_id', 1); // Admin role ID
                    });
            })
            ->pluck('id')
            ->toArray();


        if (count($authorizedExerciseIds) !== count($request->exercise_ids)) {
            return response()->json(['error' => 'Unauthorized exercise inclusion'], 403);
        }

        $template->name = $request->name;
        $template->description = $request->description;
        $template->save();

        $template->exercises()->sync($authorizedExerciseIds);

        return response()->json(['message' => 'Workout template updated successfully', 'template' => $template]);
    }

    public function destroy(WorkoutTemplate $template)
    {
        $user = Auth::user();

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

        if ($template->created_by != $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!$template->exists) {
            return response()->json(['error' => 'Workout template not found'], 404);
        }

        $template->exercises()->detach();
        $template->delete();

        return response()->json(['message' => 'Workout template deleted successfully']);
    }
}
