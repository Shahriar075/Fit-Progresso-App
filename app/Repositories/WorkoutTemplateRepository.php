<?php

namespace App\Repositories;

use App\Models\WorkoutTemplate;
use App\Models\Exercise;

class WorkoutTemplateRepository
{
    public function getAllTemplates()
    {
        return WorkoutTemplate::with('exercises')->get();
    }

    public function getUserTemplates($userId)
    {
        return WorkoutTemplate::where('created_by', $userId)
            ->orWhereHas('user', function($query) {
                $query->where('role_id', 1); // Admin role ID
            })
            ->with('exercises')
            ->get();
    }

    public function getTemplateById($templateId)
    {
        return WorkoutTemplate::with('exercises')->findOrFail($templateId);
    }

    public function getAuthorizedExerciseIds($exerciseIds, $userId)
    {
        return Exercise::whereIn('id', $exerciseIds)
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhereHas('user', function($query) {
                        $query->where('role_id', 1); // Admin role ID
                    });
            })
            ->pluck('id')
            ->toArray();
    }

    public function createTemplate($data)
    {
        return WorkoutTemplate::create($data);
    }

    public function updateTemplate(WorkoutTemplate $template, $data)
    {
        return $template->update($data);
    }

    public function deleteTemplate(WorkoutTemplate $template)
    {
        $template->exercises()->detach();
        return $template->delete();
    }
}
