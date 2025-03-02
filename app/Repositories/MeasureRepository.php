<?php

namespace App\Repositories;

use App\Models\MeasureType;
use App\Models\UserMeasure;

class MeasureRepository
{
    public function getAllMeasureTypes()
    {
        return MeasureType::all();
    }

    public function createMeasureType($data)
    {
        return MeasureType::create($data);
    }

    public function getUserMeasureHistory($user, $measureTypeId)
    {
        return UserMeasure::where('user_id', $user->id)
            ->where('measure_type_id', $measureTypeId)
            ->orderBy('recorded_on', 'desc')
            ->get(['user_id', 'value', 'recorded_on']);
    }

    public function createUserMeasure($data)
    {
        return UserMeasure::create($data);
    }

    public function updateUserMeasure($measure, $data)
    {
        $measure->update($data);
        return $measure;
    }

    public function deleteUserMeasure($measure)
    {
        $measure->delete();
    }

    public function findUserMeasure($measureId, $userId)
    {
        return UserMeasure::where('id', $measureId)
            ->where('user_id', $userId)
            ->first();
    }

    public function findMeasureType($measureTypeId)
    {
        return MeasureType::find($measureTypeId);
    }
}
