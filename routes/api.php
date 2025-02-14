<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\MeasureController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WorkoutLogController;
use App\Http\Controllers\WorkoutTemplateController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('register', [AuthController::class, 'register'])->name('register');


Route::get('auth/google', [GoogleAuthController::class, 'redirect']);
Route::get('auth/google/call-back', [GoogleAuthController::class, 'callbackGoogle']);

Route::middleware('auth:api')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::get('profile/update', [ProfileController::class, 'updateProfile']);

    Route::get('/exercises', [ExerciseController::class, 'index'])->name('exercises.index');
    Route::post('/pre-defined-exercises', [ExerciseController::class, 'createPredefinedExercise'])->name('exercises.createPredefinedExercise');
    Route::post('/exercises', [ExerciseController::class, 'store'])->name('exercises.store');
    Route::post('/exercises/{exercise}', [ExerciseController::class, 'update'])->name('exercises.update');
    Route::get('/exercises/{exercise}', [ExerciseController::class, 'destroy'])->name('exercises.destroy');
    Route::post('/exercise/search', [ExerciseController::class, 'searchExercise'])->name('exercises.searchExercise');

    Route::get('/workout-templates', [WorkoutTemplateController::class, 'index']);
    Route::post('/workout-templates', [WorkoutTemplateController::class, 'create']);
    Route::post('/workout-templates/{template}', [WorkoutTemplateController::class, 'update']);
    Route::get('/workout-templates/{template}', [WorkoutTemplateController::class, 'destroy']);

    Route::get('/workout-logs', [WorkoutLogController::class, 'index'])->name('workout-logs.index');
    Route::post('/workout-logs', [WorkoutLogController::class, 'create'])->name('workout-logs.create');
    Route::post('/workout-logs/{workoutLog}', [WorkoutLogController::class, 'update'])->name('workout-logs.update');
    Route::get('/workout-logs/{workoutLog}', [WorkoutLogController::class, 'destroy'])->name('workout-logs.destroy');
    Route::post('/workout-log/search', [ProfileController::class, 'searchWorkoutLogsByDate'])->name('workout-logs.searchWorkoutLogsByDate');
    Route::get('/workout-log/history', [ProfileController::class, 'getWorkoutLogHistory']);

    Route::get('/exercises/details/{id}', [ProfileController::class, 'getExerciseDetails']);
    Route::get('/exercises/history/{id}', [ProfileController::class, 'getExerciseHistory']);
    Route::get('/exercises/records/{id}', [ProfileController::class, 'getExerciseRecords']);

    Route::post('/measures/input', [MeasureController::class, 'inputMeasure']);
    Route::post('/measures/{measureId}', [MeasureController::class, 'updateMeasure']);
    Route::get('/measures/{measureId}', [MeasureController::class, 'deleteMeasure']);
    Route::get('/measures/history/{measureTypeId}', [MeasureController::class, 'getMeasureHistory']);
    Route::post('/measure-types', [MeasureController::class, 'createMeasureType']);

    Route::get('/users/activate/{id}', [AuthController::class, 'activateUser']);
    Route::get('/users/deactivate/{id}', [AuthController::class, 'deactivateUser']);
});

