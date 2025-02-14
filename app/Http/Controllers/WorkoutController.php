<?php

namespace App\Http\Controllers;

use App\Models\Workout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkoutController extends Controller
{
    public function index()
    {
        if (Auth::user()->isAdmin()) {
            $workouts = Workout::all();
        } else {
            $workouts = Workout::where('user_id', Auth::id())->get();
        }

        return response()->json($workouts);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'start_time' => 'nullable|date_format:Y-m-d H:i:s',
            'end_time' => 'nullable|date_format:Y-m-d H:i:s',
        ]);

        $workout = new Workout();
        $workout->name = $request->name;
        $workout->user_id = Auth::id();
        $workout->notes = $request->notes;
        $workout->start_time = $request->start_time;
        $workout->end_time = $request->end_time;
        $workout->save();

        return response()->json($workout, 201);
    }

    public function show(Workout $workout)
    {
        if (Auth::user()->isAdmin() || $workout->user_id == Auth::id()) {
            return response()->json($workout);
        }

        return response()->json(['error' => 'Unauthorized'], 403);
    }

    public function update(Request $request, Workout $workout)
    {
        if (Auth::user()->isAdmin() || $workout->user_id == Auth::id()) {
            $request->validate([
                'name' => 'required|string|max:255',
                'notes' => 'nullable|string',
                'start_time' => 'nullable|date_format:Y-m-d H:i:s',
                'end_time' => 'nullable|date_format:Y-m-d H:i:s',
            ]);

            $workout->name = $request->name;
            $workout->notes = $request->notes;
            $workout->start_time = $request->start_time;
            $workout->end_time = $request->end_time;
            $workout->save();

            return response()->json($workout);
        }

        return response()->json(['error' => 'Unauthorized'], 403);
    }

    public function destroy(Workout $workout)
    {
        if (Auth::user()->isAdmin() || $workout->user_id == Auth::id()) {
            $workout->delete();
            return response()->json(['message' => 'Workout deleted successfully']);
        }

        return response()->json(['error' => 'Unauthorized'], 403);
    }
}
