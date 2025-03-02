<?php

namespace App\Services;

use App\Repositories\ExerciseRepository;
use Illuminate\Support\Facades\Auth;

class ExerciseService
{
    protected $exerciseRepository;

    public function __construct(ExerciseRepository $exerciseRepository)
    {
        $this->exerciseRepository = $exerciseRepository;
    }

    public function getExercises()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return $this->exerciseRepository->getAllExercises();
        }

        return $this->exerciseRepository->getUserExercises($user);
    }

    public function createExercise($data)
    {
        $user = Auth::user();
        $data['user_id'] = $user->id;
        return $this->exerciseRepository->createExercise($data);
    }

    public function updateExercise($exercise, $data)
    {
        return $this->exerciseRepository->updateExercise($exercise, $data);
    }

    public function deleteExercise($exercise)
    {
        return $this->exerciseRepository->deleteExercise($exercise);
    }
}
