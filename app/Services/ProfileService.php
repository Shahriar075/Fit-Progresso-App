<?php

namespace App\Services;

use App\Models\User;
use App\Models\Exercise;
use App\Repositories\ProfileRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class ProfileService
{
    protected $profileRepository;

    public function __construct(ProfileRepository $profileRepository)
    {
        $this->profileRepository = $profileRepository;
    }

    public function getWorkoutLogHistory(User $user)
    {
        $this->checkUserActive($user);

        $workoutLogs = $this->profileRepository->getWorkoutLogs($user, $user->isAdmin());
        return $this->formatWorkoutLogs($workoutLogs);
    }

    public function updateProfile(User $user, array $data)
    {
        $this->checkUserActive($user);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->profileRepository->updateUser($user, $data);
    }

    public function getExerciseDetails(User $user, $exerciseId)
    {
        $this->checkUserActive($user);
        $exercise = $this->validateUserExerciseAccess($user, $exerciseId);

        return $exercise->instructions ?? 'Instruction not available, as it is not predefined';
    }

    public function getExerciseHistory(User $user, $exerciseId)
    {
        $this->checkUserActive($user);
        $exercise = $this->validateUserExerciseAccess($user, $exerciseId);

        $workouts = $this->profileRepository->getExerciseWorkouts($user->id, $exerciseId);
        $filteredHistory = $this->formatExerciseWorkouts($workouts);

        if (empty($filteredHistory)) {
            throw new \Exception("No exercise history found for that specified exercise", 404);
        }

        return $filteredHistory;
    }

    public function getExerciseRecords(User $user, $exerciseId)
    {
        $this->checkUserActive($user);
        $exercise = $this->validateUserExerciseAccess($user, $exerciseId);

        $records = $this->profileRepository->getExerciseRecords($exerciseId, $user->id);
        $exerciseStats = $this->calculateExerciseStats($records);

        if (empty($exerciseStats)) {
            throw new \Exception("No records found for the specified exercise", 404);
        }

        return $exerciseStats;
    }

    public function searchWorkoutLogsByDate(User $user, $startDate, $endDate)
    {
        $this->checkUserActive($user);

        $workoutLogs = $this->profileRepository->searchWorkoutLogs(
            $user,
            $user->isAdmin(),
            $startDate,
            $endDate
        );

        $formattedLogs = $this->formatWorkoutLogs($workoutLogs);

        if (empty($formattedLogs)) {
            throw new \Exception("No workout records found in this date range", 404);
        }

        return $formattedLogs;
    }

    private function checkUserActive(User $user)
    {
        try {
            $user->checkActive();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 401);
        }
    }

    private function validateUserExerciseAccess(User $user, $exerciseId)
    {
        if (!$user) {
            throw new \Exception("User not found", 404);
        }

        $exercise = $this->profileRepository->findExerciseById($exerciseId);

        if (!$exercise) {
            throw new \Exception("Exercise not found", 404);
        }

        if ($exercise->user_id != $user->id && !$exercise->isCreatedByAdmin()) {
            throw new \Exception("Unauthorized access", 403);
        }

        return $exercise;
    }

    private function formatWorkoutLogs($workoutLogs)
    {
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

        return $formattedLogs;
    }

    private function formatExerciseWorkouts($workouts)
    {
        $filteredHistory = [];

        foreach ($workouts as $workout) {
            $setsPerformed = [];

            foreach ($workout->exercises as $workoutExercise) {
                $exercise = $workoutExercise->exercise;
                $exerciseType = $exercise->type;

                foreach ($workoutExercise->sets as $set) {
                    $setsPerformed[] = $this->formatSetByExerciseType($exerciseType, $set);
                }
            }

            $filteredHistory[] = [
                'workout_name' => $workout->name,
                'date_time' => $workout->workout_date,
                'sets_performed' => $setsPerformed,
            ];
        }

        return $filteredHistory;
    }

    private function formatSetByExerciseType($exerciseType, $set)
    {
        switch ($exerciseType) {
            case 'Strength':
                return "{$set->value} kg x {$set->reps} reps";
            case 'Cardio':
                return "{$set->value} km | " . gmdate('H:i:s', $set->time_spent);
            case 'Bodyweight':
                return "{$set->reps} reps";
            case 'Flexibility':
                return "{$set->time_spent} minutes";
            default:
                return "Unknown exercise type";
        }
    }

    private function calculateExerciseStats($records)
    {
        $exerciseStats = [];

        foreach ($records as $record) {
            $stats = [
                'max_volume' => 0,
                'max_reps' => 0,
                'max_weight' => 0,
                'max_time_spent' => 0,
                'total_reps' => 0,
                'total_weight' => 0
            ];

            foreach ($record->sets as $set) {
                $stats['max_volume'] = max($stats['max_volume'], $set->value * $set->reps);
                $stats['max_reps'] = max($stats['max_reps'], $set->reps);
                $stats['max_weight'] = max($stats['max_weight'], $set->value);
                $stats['max_time_spent'] = max($stats['max_time_spent'], $set->time_spent);
                $stats['total_reps'] += $set->reps;
                $stats['total_weight'] += $set->value;
            }

            $exerciseStats[] = array_merge(
                ['workout_log_name' => $record->workoutLog->name],
                $stats
            );
        }

        return $exerciseStats;
    }
}