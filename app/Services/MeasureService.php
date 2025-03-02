<?php

namespace App\Services;

use App\Repositories\MeasureRepository;
use Illuminate\Support\Facades\Auth;

class MeasureService
{
    protected $measureRepository;

    public function __construct(MeasureRepository $measureRepository)
    {
        $this->measureRepository = $measureRepository;
    }

    public function getMeasureTypes()
    {
        return $this->measureRepository->getAllMeasureTypes();
    }

    public function createMeasureType($data)
    {
        return $this->measureRepository->createMeasureType($data);
    }

    public function getMeasureHistory($measureTypeId)
    {
        $user = Auth::user();
        return $this->measureRepository->getUserMeasureHistory($user, $measureTypeId);
    }

    public function createUserMeasure($data)
    {
        $user = Auth::user();
        $data['user_id'] = $user->id;
        return $this->measureRepository->createUserMeasure($data);
    }

    public function updateUserMeasure($measure, $data)
    {
        return $this->measureRepository->updateUserMeasure($measure, $data);
    }

    public function deleteUserMeasure($measure)
    {
        return $this->measureRepository->deleteUserMeasure($measure);
    }

    public function findUserMeasure($measureId)
    {
        $user = Auth::user();
        return $this->measureRepository->findUserMeasure($measureId, $user->id);
    }

    public function findMeasureType($measureTypeId)
    {
        return $this->measureRepository->findMeasureType($measureTypeId);
    }
}
