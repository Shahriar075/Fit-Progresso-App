<?php

namespace App\Services;

use App\Models\PersonalRecord;

class FlexibilityExercise implements ExerciseType
{
    public function getBestSet(array $sets): ?string
    {
        // Flexibility exercises use time_spent
        $bestSet = null;

        foreach ($sets as $set) {
            if (!isset($set['time_spent'])) {
                throw new \InvalidArgumentException("For flexibility exercises, time_spent is required.");
            }

            if (is_null($bestSet) || $set['time_spent'] > $bestSet['time_spent']) {
                $bestSet = $set;
            }
        }

        if ($bestSet) {
            return "{$bestSet['time_spent']} minutes";
        }

        return null;
    }

    public function getTotalWeight(array $sets): int
    {
        //Total weight is not required for the Flexibility exercises
        return 0;
    }

    public function updatePersonalRecord(int $userId, int $exerciseId, array $sets): bool
    {
        //Update the personal record when a user achieve his/her best performance (based on time_spent)
        $maxTimeSpent = 0;

        foreach ($sets as $setData) {
            $timeSpent = $setData['time_spent'] ?? 0;
            if ($timeSpent > $maxTimeSpent) {
                $maxTimeSpent = $timeSpent;
            }
        }

        $record = PersonalRecord::where('user_id', $userId)
            ->where('exercise_id', $exerciseId)
            ->where('exercise_type', 'Flexibility')
            ->first();


        $isNewRecord = !$record;


        if ($isNewRecord || $maxTimeSpent > $record->max_time_spent) {
            if (!$record) {
                $record = new PersonalRecord();
            }

            $record->user_id = $userId;
            $record->exercise_id = $exerciseId;
            $record->exercise_type = 'Flexibility';
            $record->max_time_spent = $maxTimeSpent;
            $record->save();

            return true;
        }

        return false;
    }

}
