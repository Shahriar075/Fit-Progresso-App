<?php

namespace App\Services;

use App\Repositories\WorkoutTemplateRepository;
use Exception;

class WorkoutTemplateService
{
    protected $workoutTemplateRepository;

    public function __construct(WorkoutTemplateRepository $workoutTemplateRepository)
    {
        $this->workoutTemplateRepository = $workoutTemplateRepository;
    }

    public function getTemplates($user)
    {
        if ($user->isAdmin()) {
            return $this->workoutTemplateRepository->getAllTemplates();
        } else {
            return $this->workoutTemplateRepository->getUserTemplates($user->id);
        }
    }

    public function createTemplate($user, $data)
    {
        $authorizedExerciseIds = $this->workoutTemplateRepository->getAuthorizedExerciseIds($data['exercise_ids'], $user->id);

        if (count($authorizedExerciseIds) !== count($data['exercise_ids'])) {
            throw new Exception('Unauthorized exercise inclusion');
        }

        $template = $this->workoutTemplateRepository->createTemplate([
            'name' => $data['name'],
            'created_by' => $user->id,
            'description' => $data['description'] ?? null,
        ]);

        $template->exercises()->attach($authorizedExerciseIds);

        return $template;
    }

    public function updateTemplate($user, $template, $data)
    {
        if ($template->created_by !== $user->id) {
            throw new Exception('Unauthorized');
        }

        $authorizedExerciseIds = $this->workoutTemplateRepository->getAuthorizedExerciseIds($data['exercise_ids'], $user->id);

        if (count($authorizedExerciseIds) !== count($data['exercise_ids'])) {
            throw new Exception('Unauthorized exercise inclusion');
        }

        $this->workoutTemplateRepository->updateTemplate($template, [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        $template->exercises()->sync($authorizedExerciseIds);

        return $template;
    }

    public function deleteTemplate($user, $template)
    {
        if ($template->created_by !== $user->id) {
            throw new Exception('Unauthorized');
        }

        return $this->workoutTemplateRepository->deleteTemplate($template);
    }
}
