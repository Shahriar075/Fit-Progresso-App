<?php

namespace App\Http\Controllers;

use App\Models\MeasureType;
use App\Models\UserMeasure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MeasureController extends Controller
{
    public function createMeasureType(Request $request)
    {
        $user = Auth::user();

        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:measure_types,name',
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }

        if ($user != $user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $measureType = MeasureType::create($request->only('name'));

        return response()->json(['message' => 'Measure type created successfully', 'measure_type' => $measureType], 201);
    }

    public function inputMeasure(Request $request)
    {
        $user = Auth::user();

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

        try {
            $request->validate([
                'measure_type_id' => 'required|exists:measure_types,id',
                'value' => 'required|numeric',
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $dateTime = new \DateTime();

        $userMeasure = UserMeasure::create([
            'user_id' => $user->id,
            'measure_type_id' => $request->measure_type_id,
            'value' => $request->value,
            'recorded_on' => $dateTime,
        ]);

        return response()->json(['message' => 'Measurement recorded successfully', 'user_measure' => $userMeasure], 201);
    }

    public function updateMeasure(Request $request, $measureId)
    {
        $user = Auth::user();

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

        try {
            $request->validate([
                'measure_type_id' => 'required|exists:measure_types,id',
                'value' => 'required|numeric',
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $userMeasure = UserMeasure::where('id', $measureId)
            ->where('user_id', $user->id)
            ->first();

        if (!$userMeasure) {
            return response()->json(['error' => 'Measurement record not found'], 404);
        }

        $userMeasure->update([
            'measure_type_id' => $request->measure_type_id,
            'value' => $request->value,
            'recorded_on' => new \DateTime(),
        ]);

        return response()->json(['message' => 'Measurement record updated successfully', 'user_measure' => $userMeasure], 201);
    }

    public function deleteMeasure($measureId)
    {
        $user = Auth::user();

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

        $userMeasure = UserMeasure::where('id', $measureId)
            ->where('user_id', $user->id)
            ->first();

        if (!$userMeasure) {
            return response()->json(['error' => 'Measurement record not found'], 404);
        }

        $userMeasure->delete();

        return response()->json(['message' => 'Measurement record deleted successfully']);
    }

    public function getMeasureHistory($measureTypeId)
    {
        $user = Auth::user();
        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

        $measureType = MeasureType::find($measureTypeId);

        if (!$measureType) {
            return response()->json(['error' => 'Invalid measure type'], 404);
        }

        $measureHistory = UserMeasure::where('user_id', $user->id)
            ->where('measure_type_id', $measureTypeId)
            ->orderBy('recorded_on', 'desc')
            ->get(['user_id', 'value', 'recorded_on']);

        $units = [
            'Body-weight' => '%',
            'Calorie' => 'k cal',
            'Weight' => 'kg',
        ];

        $unit = $units[$measureType->name] ?? '';

        foreach ($measureHistory as $measure) {
            $measure->value = $measure->value . ' ' . $unit;
        }

        if($measureHistory->isEmpty()){
            return response()->json(['error' => 'No measurement history found'], 404);
        }

        return response()->json($measureHistory, 201);
    }

    public function getAllMeasureTypes()
    {
        $user = Auth::user();
        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

        $measureTypes = MeasureType::all();

        return response()->json($measureTypes);
    }
}
