<?php

namespace App\Http\Controllers;

use App\Services\WorkoutTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class WorkoutTemplateController extends Controller
{
    protected $workoutTemplateService;

    public function __construct(WorkoutTemplateService $workoutTemplateService)
    {
        $this->workoutTemplateService = $workoutTemplateService;
    }

    public function index()
    {
        $user = Auth::user();

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

        try {
            $templates = $this->workoutTemplateService->getTemplates($user);
            if ($templates->isEmpty()) {
                return response()->json(['error' => 'No templates found.'], 404);
            }
            return response()->json($templates);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }

    public function create(Request $request)
    {
        $user = Auth::user();

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'exercise_ids' => 'required|array',
            'exercise_ids.*' => 'exists:exercises,id',
        ]);

        try {
            $template = $this->workoutTemplateService->createTemplate($user, $validated);
            return response()->json(['message' => 'Workout template created successfully', 'template' => $template]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }

    public function show($templateId)
    {
        $user = Auth::user();

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

        try {
            $template = $this->workoutTemplateService->getTemplateById($templateId);
            if ($user->isAdmin() || $template->created_by === $user->id) {
                return response()->json($template->load('exercises'));
            } else {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function update(Request $request, $templateId)
    {
        $user = Auth::user();

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'exercise_ids' => 'required|array',
            'exercise_ids.*' => 'exists:exercises,id',
        ]);

        try {
            $template = $this->workoutTemplateService->updateTemplate($user, $templateId, $validated);
            return response()->json(['message' => 'Workout template updated successfully', 'template' => $template]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }

    public function destroy($templateId)
    {
        $user = Auth::user();

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

        try {
            $this->workoutTemplateService->deleteTemplate($user, $templateId);
            return response()->json(['message' => 'Workout template deleted successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }
}
