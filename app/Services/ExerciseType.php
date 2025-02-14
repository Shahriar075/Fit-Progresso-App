<?php

namespace App\Services;

interface ExerciseType
{
    public function getBestSet(array $sets): ?string;
    public function getTotalWeight(array $sets): int;
    public function updatePersonalRecord(int $userId, int $exerciseId, array $setData): bool;

}

