<?php
namespace App\Services;

use App\Models\PersonalRecord;

class StrengthExercise implements ExerciseType
{

    public function getBestSet(array $sets): ?string
    {
        //Strength exercises use both value and reps
        $bestSet = null;

        foreach ($sets as $set) {
            if (!isset($set['value']) || !isset($set['reps'])) {
                throw new \InvalidArgumentException("For strength exercises, both value and reps are required.");
            }

            $currentWeight = $set['value'] * $set['reps'];

            if (is_null($bestSet)) {
                $bestSet = $set;
            } else {
                $bestWeight = $bestSet['value'] * $bestSet['reps'];
                if ($currentWeight > $bestWeight) {
                    $bestSet = $set;
                }
            }
        }

        if ($bestSet) {
            return "{$bestSet['value']} kg x {$bestSet['reps']} reps";
        }

        return null;
    }



    public function getTotalWeight(array $sets): int
    {
        //Total weight is required for the Strength exercises
        $totalWeight = 0;

        foreach ($sets as $set) {
            if (isset($set['value']) && isset($set['reps'])) {
                $totalWeight += $set['value'] * $set['reps'];
            }
        }

        return $totalWeight;
    }

    public function updatePersonalRecord(int $userId, int $exerciseId, array $sets): bool
    {
        //Update the personal record when a user achieve his/her best performance (based on total_volume(weight*reps))
        $bestVolume = 0;
        $bestValue = 0;
        $bestReps = 0;

        foreach ($sets as $setData) {
            $value = $setData['value'] ?? 0;
            $reps = $setData['reps'] ?? 0;

            $totalVolume = $value * $reps;

            if ($totalVolume > $bestVolume) {
                $bestVolume = $totalVolume;
                $bestValue = $value;
                $bestReps = $reps;
            }
        }

        $record = PersonalRecord::where('user_id', $userId)
            ->where('exercise_id', $exerciseId)
            ->where('exercise_type', 'Strength')
            ->first();

        $isNewRecord = !$record;

        if ($isNewRecord || $bestVolume > ($record->max_value * $record->max_reps)) {
            if (!$record) {
                $record = new PersonalRecord();
            }

            $record->user_id = $userId;
            $record->exercise_id = $exerciseId;
            $record->exercise_type = 'Strength';
            $record->max_value = $bestValue;
            $record->max_reps = $bestReps;
            $record->save();

            return true;
        }

        return false;
    }

}
