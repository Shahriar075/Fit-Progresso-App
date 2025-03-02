<?php

namespace App\Http\Controllers;

use App\Services\ProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    protected $profileService;

    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    public function getWorkoutLogHistory()
    {
        $user = Auth::user();

        try {
            return response()->json(
                $this->profileService->getWorkoutLogHistory($user),
                201
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        try {
            $validatedData = $request->validate([
                'name' => 'nullable|string|max:255',
                'email' => 'nullable|string|email|max:255|unique:users,email,' . $user->id,
                'password' => 'nullable|string|confirmed|min:8',
                'role_id' => 'nullable|exists:roles,id',
            ]);

            $updatedUser = $this->profileService->updateProfile($user, $validatedData);

            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => $updatedUser
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    public function getExerciseDetails($id)
    {
        $user = Auth::user();

        try {
            $instructions = $this->profileService->getExerciseDetails($user, $id);
            return response()->json(['instructions' => $instructions]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    public function getExerciseHistory($exerciseId)
    {
        $user = Auth::user();

        try {
            $history = $this->profileService->getExerciseHistory($user, $exerciseId);
            return response()->json($history);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    public function getExerciseRecords($exerciseId)
    {
        $user = Auth::user();

        try {
            $records = $this->profileService->getExerciseRecords($user, $exerciseId);
            return response()->json($records);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    public function searchWorkoutLogsByDate(Request $request)
    {
        $user = Auth::user();

        try {
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $logs = $this->profileService->searchWorkoutLogsByDate($user, $startDate, $endDate);
            return response()->json($logs, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}