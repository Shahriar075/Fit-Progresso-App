<?php

namespace Tests\Unit;

use App\Models\Exercise;
use App\Models\User;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProfileControllerTest extends TestCase
{
    public function testAdminCanViewAllWorkoutLogHistory()
    {
        $user = User::where('email', 'admin@gmail.com')->first();
        $this->assertNotNull($user, 'User not found with the given email');

        $this->assertTrue($user->isAdmin(), 'The user should be an admin user');

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/workout-log/history');

        $response->assertStatus(201);

        dd($response->json());
    }

    public function testGeneralUserCanViewOwnWorkoutLogHistory()
    {
        $user = User::where('email', 'ashikur@gmail.com')->first();
        $this->assertNotNull($user, 'User not found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/workout-log/history');

        $response->assertStatus(201);

        dd($response->json());
    }

    public function testUpdateProfileWithValidFields()
    {
        $user = User::where('email', 'ashikur@gmail.com')->first();
        $this->assertNotNull($user, 'User not found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/profile/update' , [
            'name' => 'Ashik',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role_id' => 2,
        ]);

        $response->assertStatus(200);

        dd($response->json());
    }

    public function testUpdateProfileWithInvalidFields()
    {
        $user = User::where('email', 'ashikur@gmail.com')->first();
        $this->assertNotNull($user, 'User not found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/profile/update' , [
            'name' => 'Ashik',
            'password' => 'password',
            'password_confirmation' => 'passwor',
            'role_id' => 2,
        ]);

        $response->assertStatus(500);

        dd($response->json());
    }

    public function testGetSpecificExercisesAvailableInstructions()
    {
        $user = User::where('email', 'ashikur@gmail.com')->first();
        $this->assertNotNull($user, 'User not found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $id = 15;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/exercises/details/$id}");

        $response->assertStatus(200);

        dd($response->json());
    }

    public function testGetSpecificExercisesNotAvailableInstructions()
    {
        $user = User::where('email', 'ashikur@gmail.com')->first();
        $this->assertNotNull($user, 'User not found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $id = 4;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/exercises/details/$id}");

        $response->assertStatus(200);

        dd($response->json());
    }

    public function testGetSpecificUnauthorizedExercisesInstructions()
    {
        $user = User::where('email', 'ashikur@gmail.com')->first();
        $this->assertNotNull($user, 'User not found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $id = 16;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/exercises/details/$id}");

        $response->assertStatus(403);

        dd($response->json());
    }

    public function testGetExerciseInstructionsWithoutAuthorization()
    {
        $id = 16;

        $response = $this->getJson("/api/exercises/records/$id}");

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);

        dd($response->json());
    }

    public function testGetPerformedExerciseHistory()
    {
        $user = User::where('email', 'ashikur@gmail.com')->first();
        $this->assertNotNull($user, 'User not found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $id = 2;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/exercises/history/$id}");

        $response->assertStatus(200);

        dd($response->json());
    }

    public function testGetNotPerformedExerciseHistory()
    {
        $user = User::where('email', 'ashikur@gmail.com')->first();
        $this->assertNotNull($user, 'User not found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $id = 4;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/exercises/history/$id}");

        $response->assertStatus(404);

        dd($response->json());
    }

    public function testGetUnauthorizedExerciseHistory()
    {
        $user = User::where('email', 'ashikur@gmail.com')->first();
        $this->assertNotNull($user, 'User not found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $id = 16;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/exercises/history/$id}");

        $response->assertStatus(403);

        dd($response->json());
    }

    public function testGetExerciseHistoryWithoutAuthorization()
    {
        $id = 16;

        $response = $this->getJson("/api/exercises/history/$id}");

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);

        dd($response->json());
    }

    public function testGetPerformedExerciseRecords()
    {
        $user = User::where('email', 'ashikur@gmail.com')->first();
        $this->assertNotNull($user, 'User not found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $id = 2;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/exercises/records/$id}");

        $response->assertStatus(200);

        dd($response->json());
    }

    public function testGetNotPerformedExerciseRecords()
    {
        $user = User::where('email', 'ashikur@gmail.com')->first();
        $this->assertNotNull($user, 'User not found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $id = 4;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/exercises/records/$id}");

        $response->assertStatus(404);

        dd($response->json());
    }

    public function testGetUnauthorizedExerciseRecords()
    {
        $user = User::where('email', 'ashikur@gmail.com')->first();
        $this->assertNotNull($user, 'User not found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $id = 16;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/exercises/records/$id}");

        $response->assertStatus(403);

        dd($response->json());
    }

    public function testGetExerciseRecordsWithoutAuthorization()
    {
        $id = 16;

        $response = $this->getJson("/api/exercises/records/$id}");

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);

        dd($response->json());
    }

    public function testAdminCanSearchAllUsersWorkoutLogsByValidDateFormat()
    {
        $user = User::where('email', 'admin@gmail.com')->first();
        $this->assertNotNull($user, 'User not found with the given email');

        $this->assertTrue($user->isAdmin(), 'The user should be an admin user');

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/workout-log/search', [
            'start_date' => '2024-08-10',
            'end_date' => '2024-08-15',
        ]);

        $response->assertStatus(200);

        dd($response->json());
    }

    public function testGeneralUserCanSearchOwnWorkoutLogsByValidDateFormat()
    {
        $user = User::where('email', 'ashikur@gmail.com')->first();
        $this->assertNotNull($user, 'User not found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/workout-log/search', [
            'start_date' => '2024-08-10',
            'end_date' => '2024-08-15',
        ]);

        $response->assertStatus(200);

        dd($response->json());
    }

    public function testGeneralUserCanSearchOwnWorkoutLogsByInvalidDateFormat()
    {
        $user = User::where('email', 'ashikur@gmail.com')->first();
        $this->assertNotNull($user, 'User not found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/workout-log/search', [
            'start_date' => '2024-08-16',
            'end_date' => '2024-08-15',
        ]);

        $response->assertStatus(422);

        dd($response->json());
    }

    public function testGeneralUserCanSearchOwnWorkoutLogsByOutOfRangeDateFormat()
    {
        $user = User::where('email', 'ashikur@gmail.com')->first();
        $this->assertNotNull($user, 'User not found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/workout-log/search', [
            'start_date' => '2024-08-10',
            'end_date' => '2024-08-10',
        ]);

        $response->assertStatus(404);

        dd($response->json());
    }

    public function testSearchWorkoutLogsWithoutAuthorization()
    {
        $response = $this->postJson('/api/workout-log/search',[
            'start_date' => '2024-08-10',
            'end_date' => '2024-08-10',
        ]);

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