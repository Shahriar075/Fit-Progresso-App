<?php

namespace App\Services;

use App\Models\PersonalRecord;

class CardioExercise implements ExerciseType
{
    public function getBestSet(array $sets): ?string
    {
        //Cardio exercises use both value and time_spent
        $bestSet = null;
        $bestPerformance = 0;

        foreach ($sets as $set) {
            if (!isset($set['value']) || !isset($set['time_spent'])) {
                throw new \InvalidArgumentException("For cardio exercises, both value (distance) and time_spent are required.");
            }

            $performance = $set['value'] * $set['time_spent'];

            if ($performance > $bestPerformance) {
                $bestPerformance = $performance;
                $bestSet = $set;
            }
        }

        if ($bestSet) {
            return "{$bestSet['value']} km | " . gmdate('H:i:s', $bestSet['time_spent']);
        }

        return null;
    }


    public function getTotalWeight(array $sets): int
    {
        //Total weight is not required for the Cardio exercises
        return 0;
    }

    public function updatePersonalRecord(int $userId, int $exerciseId, array $sets): bool
    {
        //Update the personal record when a user achieve his/her best performance (based on distance*time)
        $bestPerformanceMetric = 0;

        foreach ($sets as $setData) {
            $value = $setData['value'] ?? 0;
            $timeSpent = $setData['time_spent'] ?? 0;
            $performanceMetric = $value * $timeSpent;

            if ($performanceMetric > $bestPerformanceMetric) {
                $bestPerformanceMetric = $performanceMetric;
                $bestValue = $value;
                $bestTimeSpent = $timeSpent;
            }
        }

        $record = PersonalRecord::where('user_id', $userId)
            ->where('exercise_id', $exerciseId)
            ->where('exercise_type', 'Cardio')
            ->first();

        $isNewRecord = !$record;
        $recordMetric = $record ? $record->max_value * $record->max_time_spent : 0;

        if ($isNewRecord || $bestPerformanceMetric > $recordMetric) {
            if (!$record) {
                $record = new PersonalRecord();
            }

            $record->user_id = $userId;
            $record->exercise_id = $exerciseId;
            $record->exercise_type = 'Cardio';
            $record->max_value = $bestValue;
            $record->max_time_spent = $bestTimeSpent;
            $record->save();

            return true;
        }

        return false;
    }

}
