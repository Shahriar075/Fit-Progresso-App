<?php

namespace Tests\Unit;

use App\Models\Exercise;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkoutLog;
use App\Models\WorkoutTemplate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class WorkoutLogControllerTest extends TestCase
{

    public function testAdminCanViewAllWorkoutLogs()
    {
        $user = User::where('email', 'admin@gmail.com')->first();
        $this->assertNotNull($user, 'No admin user found with the given email');

        $this->assertTrue($user->isAdmin(), 'The user should be an admin user');

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/workout-logs');

        $response->assertStatus(200);

        dd($response->json());
    }

    public function testGeneralUserCanViewOwnWorkoutLogs()
    {
        $user = User::where('email', 'sajid@gmail.com')->first();
        $this->assertNotNull($user, 'No user found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/workout-logs');

        $response->assertStatus(200);

        dd($response->json());
    }

    public function testWithoutAnyWorkoutLogsFound()
    {
        $user = User::where('email', 'sajid@gmail.com')->first();
        $this->assertNotNull($user, 'No user found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/workout-logs');

        $response->assertStatus(404);

        dd($response->json());
    }

    public function testCreateWorkoutLogWithValidExercises()
    {
        $user = User::where('email', 'sajid@gmail.com')->first();
        $this->assertNotNull($user, 'No user found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $exerciseIds = [9, 16];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/workout-logs', [
            'name' => 'Workout Log 1st by Sajid',
            'total_duration' => 1200,
            'workout_date' => '2024-08-13',
            'exercises' => [
                [
                    'exercise_id' => $exerciseIds[0],
                    'sets' => [
                        ['set_number' => 1, 'value' => 50, 'reps' => 2],
                        ['set_number' => 2, 'value' => 10, 'reps' => 12],
                    ],
                ],
                [
                    'exercise_id' => $exerciseIds[1],
                    'sets' => [
                        ['set_number' => 1, 'value' => 5, 'time_spent' => 600],
                    ],
                ],
            ],
        ]);

        $response->assertStatus(201);

        dd($response->json());
    }

    public function testCreateWorkoutLogWithInvalidExercises()
    {
        $user = User::where('email', 'sajid@gmail.com')->first();
        $this->assertNotNull($user, 'No user found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $invalidExerciseIds = [9, 1];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/workout-logs', [
            'name' => 'Invalid Workout Log by Sajid',
            'total_duration' => 1200,
            'workout_date' => '2024-08-13',
            'exercises' => [
                [
                    'exercise_id' => $invalidExerciseIds[0],
                    'sets' => [
                        ['set_number' => 1, 'value' => 60, 'reps' => 4]
                    ],
                ],
                [
                    'exercise_id' => $invalidExerciseIds[1],
                    'sets' => [
                        ['set_number' => 1, 'value' => 10, 'reps' => 12],
                    ],
                ],
            ],
        ]);

        $response->assertStatus(422);

        dd($response->json());
    }

    public function testCreateWorkoutLogWithInvalidSetData()
    {
        $user = User::where('email', 'sajid@gmail.com')->first();
        $this->assertNotNull($user, 'No user found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $invalidExerciseIds = [9, 16];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/workout-logs', [
            'name' => 'Workout Log 2nd by Sajid',
            'total_duration' => 1200,
            'workout_date' => '2024-08-13',
            'exercises' => [
                [
                    'exercise_id' => $invalidExerciseIds[0],
                    'sets' => [
                        ['set_number' => 1, 'value' => 60, 'reps' => 4]
                    ],
                ],
                [
                    'exercise_id' => $invalidExerciseIds[1],
                    'sets' => [
                        ['set_number' => 1, 'value' => 10],
                    ],
                ],
            ],
        ]);

        $response->assertStatus(422);

        dd($response->json());
    }

    public function testUpdateWorkoutLogWithValidExercises()
    {
        $user = User::where('email', 'sajid@gmail.com')->first();
        $this->assertNotNull($user, 'No user found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $workoutLog = WorkoutLog::where('user_id', $user->id)->first();

        $exerciseIds = [9, 16];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/workout-logs/{$workoutLog->id}", [
            'name' => 'Workout Log 1st updated by Sajid',
            'total_duration' => 1800,
            'workout_date' => '2024-08-13',
            'exercises' => [
                [
                    'exercise_id' => $exerciseIds[0],
                    'sets' => [
                        ['set_number' => 1, 'value' => 100, 'reps' => 4],
                    ],
                ],
                [
                    'exercise_id' => $exerciseIds[1],
                    'sets' => [
                        ['set_number' => 1, 'value' => 8, 'time_spent' => 480],
                    ],
                ]
            ],
        ]);

        $response->assertStatus(201);

        dd($response->json());
    }

    public function testUpdateWorkoutLogWithInvalidExercises()
    {
        $user = User::where('email', 'sajid@gmail.com')->first();
        $this->assertNotNull($user, 'No user found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $workoutLog = WorkoutLog::where('user_id', $user->id)->first();
        $this->assertNotNull($workoutLog, 'No workout log found for the user');

        $invalidExerciseIds = [9, 1];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/workout-logs/{$workoutLog->id}", [
            'name' => 'Updated Workout Log by Sajid',
            'total_duration' => 1800,
            'workout_date' => '2024-08-13',
            'exercises' => [
                [
                    'exercise_id' => $invalidExerciseIds[0],
                    'sets' => [
                        ['set_number' => 1, 'value' => 60, 'reps' => 4],
                        ['set_number' => 2, 'value' => 10, 'reps' => 12],
                    ],
                ],
                [
                    'exercise_id' => $invalidExerciseIds[1],
                    'sets' => [
                        ['set_number' => 1, 'value' => 5, 'time_spent' => 600],
                    ],
                ],
            ],
        ]);

        $response->assertStatus(422);

        dd($response->json());
    }

    public function testUpdateWorkoutLogWithInvalidSetData()
    {
        $user = User::where('email', 'sajid@gmail.com')->first();
        $this->assertNotNull($user, 'No user found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $workoutLog = WorkoutLog::where('user_id', $user->id)->first();
        $this->assertNotNull($workoutLog, 'No workout log found for the user');

        $exerciseIds = [9, 16];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/workout-logs/{$workoutLog->id}", [
            'name' => 'Updated Workout Log by Sajid',
            'total_duration' => 1800,
            'workout_date' => '2024-08-13',
            'exercises' => [
                [
                    'exercise_id' => $exerciseIds[0],
                    'sets' => [
                        ['set_number' => 1, 'value' => 5000, 'reps' => 4],
                        ['set_number' => 2, 'value' => 10, 'reps' => 12],
                    ],
                ],
                [
                    'exercise_id' => $exerciseIds[1],
                    'sets' => [
                        ['set_number' => 1, 'value' => 5],
                    ],
                ],
            ],
        ]);

        $response->assertStatus(422);

        dd($response->json());
    }

    public function testUpdateUnauthorizedWorkoutLog()
    {
        $user = User::where('email', 'sajid@gmail.com')->first();
        $this->assertNotNull($user, 'No user found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $workoutLog = WorkoutLog::whereNot('user_id', $user->id)->first();
        $this->assertNotNull($workoutLog, 'No workout log found for the user');

        $invalidExerciseIds = [9, 1];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/workout-logs/{$workoutLog->id}", [
            'name' => 'Updated Workout Log by Sajid',
            'total_duration' => 1800,
            'workout_date' => '2024-08-13',
            'exercises' => [
                [
                    'exercise_id' => $invalidExerciseIds[0],
                    'sets' => [
                        ['set_number' => 1, 'value' => 60, 'reps' => 4],
                        ['set_number' => 2, 'value' => 10, 'reps' => 12],
                    ],
                ],
                [
                    'exercise_id' => $invalidExerciseIds[1],
                    'sets' => [
                        ['set_number' => 1, 'value' => 5, 'time_spent' => 600],
                    ],
                ],
            ],
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'You do not have permission to update this workout log.',
            ]);

        dd($response->json());
    }

    public function testDestroyAuthorizedWorkoutLog()
    {
        $user = User::where('email', 'sajid@gmail.com')->first();
        $this->assertNotNull($user, 'No user found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $workoutLog = WorkoutLog::where('user_id', $user->id)->first();
        $this->assertNotNull($workoutLog, 'No workout log found for the user');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/workout-logs/{$workoutLog->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Workout log deleted'
            ]);

        dd($response->json());

    }

    public function testDestroyUnauthorizedWorkoutLog()
    {
        $user = User::where('email', 'sajid@gmail.com')->first();
        $this->assertNotNull($user, 'No user found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $workoutLog = WorkoutLog::whereNot('user_id', $user->id)->first();
        $this->assertNotNull($workoutLog, 'No workout log found for the user');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/workout-logs/{$workoutLog->id}");

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'Unauthorized',
            ]);

        dd($response->json());
    }

    public function testWorkoutLogsWithoutAuthorization()
    {
        $response = $this->getJson("/api/workout-logs");

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);

        dd($response->json());
    }

    protected function handleInactiveUser($message)
    {
        dd("message: " . $message);
        return response($message, 403);
    }

}