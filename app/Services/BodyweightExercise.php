<?php

namespace App\Services;

use App\Models\PersonalRecord;

class BodyweightExercise implements ExerciseType
{
    public function getBestSet(array $sets): ?string
    {
        //Bodyweight exercises use reps
        $bestSet = null;

        foreach ($sets as $set) {
            if (!isset($set['reps'])) {
                throw new \InvalidArgumentException("For bodyweight exercises, reps are required.");
            }

            if (is_null($bestSet) || $set['reps'] > $bestSet['reps']) {
                $bestSet = $set;
            }
        }

        if ($bestSet) {
            return "{$bestSet['reps']} reps";
        }

        return null;
    }

    public function getTotalWeight(array $sets): int
    {
        //Total weight is not required for the Bodyweight exercises
        return 0;
    }

    public function updatePersonalRecord(int $userId, int $exerciseId, array $sets): bool
    {
        //Update the personal record when a user achieve his/her best performance (based on the reps)
        $maxReps = 0;

        foreach ($sets as $setData) {
            $reps = $setData['reps'] ?? 0;
            if ($reps > $maxReps) {
                $maxReps = $reps;
            }
        }

        $record = PersonalRecord::where('user_id', $userId)
            ->where('exercise_id', $exerciseId)
            ->where('exercise_type', 'Bodyweight')
            ->first();

        $isNewRecord = !$record;

        if ($isNewRecord || $maxReps > $record->max_reps) {
            if (!$record) {
                $record = new PersonalRecord();
            }

            $record->user_id = $userId;
            $record->exercise_id = $exerciseId;
            $record->exercise_type = 'Bodyweight';
            $record->max_reps = $maxReps;
            $record->save();

            return true;
        }

        return false;
    }

}
