<?php

namespace App\Services;

class ExerciseTypeFactory
{
    public static function create(string $type): ExerciseType
    {
        switch ($type) {
            case 'Strength':
            case 'Legs':
                return new StrengthExercise();
            case 'Cardio':
                return new CardioExercise();
            case 'Flexibility':
            case 'Balance':
            case 'Endurance':
                return new FlexibilityExercise();
            case 'Bodyweight':
                return new BodyweightExercise();
            default:
                throw new \InvalidArgumentException('Unknown exercise type');
        }
    }
}
