<?php

namespace App\Services;

use App\Models\Exercise;
use App\Models\WorkoutLog;
use App\Repositories\WorkoutLogRepository;
use App\Services\ExerciseTypeFactory;

class WorkoutLogService
{
    protected $workoutLogRepository;
    protected $exerciseTypeFactory;

    public function __construct(
        WorkoutLogRepository $workoutLogRepository,
        ExerciseTypeFactory $exerciseTypeFactory
    ) {
        $this->workoutLogRepository = $workoutLogRepository;
        $this->exerciseTypeFactory = $exerciseTypeFactory;
    }

    public function getAllWorkoutLogs($user)
    {
        if ($user->isAdmin()) {
            $workoutLogs = $this->workoutLogRepository->getAllWorkoutLogs();
        } else {
            $workoutLogs = $this->workoutLogRepository->getUserWorkoutLogs($user->id);
        }

        if ($workoutLogs->isEmpty()) {
            return ['error' => 'No workout logs found.'];
        }

        return $workoutLogs;
    }

    public function createWorkoutLog(array $data, $user)
    {
        $processedData = $this->processExercisesData($data, $user);

        if (isset($processedData['errors'])) {
            return ['errors' => $processedData['errors']];
        }

        $workoutLogData = [
            'name' => $data['name'],
            'total_duration' => $data['total_duration'],
            'workout_date' => $data['workout_date'],
            'user_id' => $user->id,
            'total_weight' => $processedData['total_weight'],
            'personal_records' => $processedData['pr_count'],
        ];

        $workoutLog = $this->workoutLogRepository->createWorkoutLog($workoutLogData);

        $this->saveExercisesAndSets($workoutLog, $data['exercises'], $processedData['set_store']);

        return $this->formatResponse($data, $processedData);
    }

    public function updateWorkoutLog(array $data, WorkoutLog $workoutLog, $user)
    {
        $processedData = $this->processExercisesData($data, $user);

        if (isset($processedData['errors'])) {
            return ['errors' => $processedData['errors']];
        }

        $workoutLogData = [
            'name' => $data['name'],
            'total_duration' => $data['total_duration'],
            'workout_date' => $data['workout_date'],
            'total_weight' => $processedData['total_weight'],
            'personal_records' => $processedData['pr_count'],
        ];

        $this->workoutLogRepository->updateWorkoutLog($workoutLog, $workoutLogData);

        // Remove existing exercises and sets
        $this->workoutLogRepository->deleteWorkoutLogExercises($workoutLog);

        // Add new exercises and sets
        $this->saveExercisesAndSets($workoutLog, $data['exercises'], $processedData['set_store']);

        return $this->formatResponse($data, $processedData);
    }

    public function deleteWorkoutLog(WorkoutLog $workoutLog, $user)
    {
        if ($workoutLog->user_id !== $user->id) {
            return ['error' => 'Unauthorized'];
        }

        $this->workoutLogRepository->deleteWorkoutLog($workoutLog);

        return ['success' => true];
    }

    protected function processExercisesData(array $data, $user)
    {
        $totalWeight = 0;
        $prCount = 0;
        $exerciseDetails = [];
        $errors = [];
        $setStore = [];

        if (!isset($data['exercises'])) {
            return [
                'total_weight' => $totalWeight,
                'pr_count' => $prCount,
                'exercise_details' => $exerciseDetails,
                'set_store' => $setStore
            ];
        }

        foreach ($data['exercises'] as $index => $exerciseData) {
            $result = $this->processExercise($exerciseData, $user, $index);

            $errors = array_merge($errors, $result['errors']);
            if (empty($result['errors'])) {
                $totalWeight += $result['weight'];
                $prCount += $result['pr_count'];
                $exerciseDetails[] = $result['details'];
                $setStore[$index] = $result['best_set'];
            }
        }

        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        return [
            'total_weight' => $totalWeight,
            'pr_count' => $prCount,
            'exercise_details' => $exerciseDetails,
            'set_store' => $setStore
        ];
    }

    protected function processExercise(array $exerciseData, $user, $index)
    {
        $errors = [];
        $exercise = Exercise::find($exerciseData['exercise_id']);
        $exerciseType = $exercise->type;
        $totalWeight = 0;
        $prCount = 0;
        $bestSet = null;

        if ($exercise->user_id !== $user->id && !$exercise->isCreatedByAdmin()) {
            return [
                'errors' => ['You do not have permission to use exercise ID ' . $exerciseData['exercise_id']],
                'weight' => 0,
                'pr_count' => 0,
                'details' => null,
                'best_set' => null
            ];
        }

        $exerciseTypeInstance = $this->exerciseTypeFactory->create($exerciseType);

        try {
            $bestSet = $exerciseTypeInstance->getBestSet($exerciseData['sets']);
            if (is_string($bestSet) && strpos($bestSet, 'error') !== false) {
                return [
                    'errors' => [$bestSet],
                    'weight' => 0,
                    'pr_count' => 0,
                    'details' => null,
                    'best_set' => null
                ];
            }
        } catch (\InvalidArgumentException $e) {
            return [
                'errors' => [$e->getMessage()],
                'weight' => 0,
                'pr_count' => 0,
                'details' => null,
                'best_set' => null
            ];
        }

        $sets = [];
        foreach ($exerciseData['sets'] as $setData) {
            $weight = $setData['value'] ?? 0;
            $reps = $setData['reps'] ?? 0;

            if ($exerciseType === 'Cardio' || $exerciseType === 'Bodyweight' || $exerciseType === 'Flexibility') {
                $weight = 0;
            }
            $totalWeight += $weight * $reps;
            $sets[] = $setData;
        }

        if ($exerciseTypeInstance->updatePersonalRecord($user->id, $exercise->id, $sets)) {
            $prCount = 1;
        }

        return [
            'errors' => [],
            'weight' => $totalWeight,
            'pr_count' => $prCount,
            'details' => [
                'exercise_name' => $exercise->name,
                'best_set' => $bestSet
            ],
            'best_set' => $bestSet
        ];
    }

    protected function saveExercisesAndSets(WorkoutLog $workoutLog, array $exercises, array $setStore)
    {
        foreach ($exercises as $index => $exerciseData) {
            $exercise = Exercise::find($exerciseData['exercise_id']);

            $workoutLogExercise = $this->workoutLogRepository->createWorkoutLogExercise([
                'workout_log_id' => $workoutLog->id,
                'exercise_id' => $exercise->id,
                'best_set' => $setStore[$index] ?? null,
            ]);

            $this->saveSets($workoutLogExercise->id, $exerciseData['sets']);
        }
    }

    protected function saveSets($workoutLogExerciseId, array $sets)
    {
        foreach ($sets as $setData) {
            $this->workoutLogRepository->createWorkoutLogSet([
                'workout_log_exercise_id' => $workoutLogExerciseId,
                'set_number' => $setData['set_number'],
                'value' => $setData['value'] ?? 0,
                'reps' => $setData['reps'] ?? 0,
                'time_spent' => $setData['time_spent'] ?? null,
            ]);
        }
    }

    protected function formatResponse(array $data, array $processedData)
    {
        return [
            'message' => 'Congratulations!!!!',
            'workout_log_name' => $data['name'],
            'date' => $data['workout_date'],
            'duration' => $data['total_duration'] ?? 0,
            'total_weight' => "{$processedData['total_weight']} kg",
            'personal_records' => "{$processedData['pr_count']} PRs",
            'exercises' => $processedData['exercise_details'],
        ];
    }
}