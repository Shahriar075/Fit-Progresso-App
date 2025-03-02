<?php

namespace App\Http\Controllers;

use App\Services\MeasureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MeasureController extends Controller
{
    protected $measureService;

    public function __construct(MeasureService $measureService)
    {
        $this->measureService = $measureService;
    }

    public function createMeasureType(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255|unique:measure_types,name',
        ]);

        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $measureType = $this->measureService->createMeasureType($request->only('name'));

        return response()->json(['message' => 'Measure type created successfully', 'measure_type' => $measureType], 201);
    }

    public function inputMeasure(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'measure_type_id' => 'required|exists:measure_types,id',
            'value' => 'required|numeric',
        ]);

        $measureData = [
            'measure_type_id' => $request->measure_type_id,
            'value' => $request->value,
            'recorded_on' => now(),
        ];

        $userMeasure = $this->measureService->createUserMeasure($measureData);

        return response()->json(['message' => 'Measurement recorded successfully', 'user_measure' => $userMeasure], 201);
    }

    public function updateMeasure(Request $request, $measureId)
    {
        $user = Auth::user();

        $request->validate([
            'measure_type_id' => 'required|exists:measure_types,id',
            'value' => 'required|numeric',
        ]);

        $userMeasure = $this->measureService->findUserMeasure($measureId);

        if (!$userMeasure) {
            return response()->json(['error' => 'Measurement record not found'], 404);
        }

        $updatedMeasure = $this->measureService->updateUserMeasure($userMeasure, $request->all());

        return response()->json(['message' => 'Measurement record updated successfully', 'user_measure' => $updatedMeasure], 200);
    }

    public function deleteMeasure($measureId)
    {
        $userMeasure = $this->measureService->findUserMeasure($measureId);

        if (!$userMeasure) {
            return response()->json(['error' => 'Measurement record not found'], 404);
        }

        $this->measureService->deleteUserMeasure($userMeasure);

        return response()->json(['message' => 'Measurement record deleted successfully']);
    }

    public function getMeasureHistory($measureTypeId)
    {
        $measureHistory = $this->measureService->getMeasureHistory($measureTypeId);

        if ($measureHistory->isEmpty()) {
            return response()->json(['error' => 'No measurement history found'], 404);
        }

        return response()->json($measureHistory, 200);
    }

    public function getAllMeasureTypes()
    {
        $measureTypes = $this->measureService->getMeasureTypes();

        return response()->json($measureTypes, 200);
    }
}
