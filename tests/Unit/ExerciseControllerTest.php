<?php

namespace Tests\Unit;

use App\Models\Exercise;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ExerciseControllerTest extends TestCase
{
    public function testIndexExercises()
    {
        $user = User::where('email', 'ashikur@gmail.com')->first();
        $this->assertNotNull($user, "No user found");

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->getJson('/api/exercises');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            '*' => ['id', 'name', 'type', 'description', 'user_id']
        ]);

        dd($response->json());
    }

    public function testStoreExercise()
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
        ])->postJson('/api/exercises',  [
            'name' => 'Mountain Climbers',
            'type' => 'Cardio',
            'description' => 'A Cardio exercise',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'exercise' => [
                    'id', 'name', 'type', 'description', 'user_id'
                ]
            ]);
        dd($response->json());
    }

    public function testUpdateExercise()
    {
        $user = User::where('email', 'sajid@gmail.com')->first();
        $this->assertNotNull($user, 'No user found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $exercise = Exercise::where('id', 6)
            ->where('user_id', $user->id)
            ->first();

        $this->assertNotNull($exercise, 'No exercise found with the given name for this user');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/exercises/{$exercise->id}", [
            'name' => 'Updated Squats',
            'type' => 'Strength',
            'description' => 'An updated description for squats',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'exercise' => [
                    'id', 'name', 'type', 'description', 'user_id'
                ]
            ]);
        dd($response->json());
    }

    public function testDestroyExercise()
    {
        $user = User::where('email', 'sajid@gmail.com')->first();
        $this->assertNotNull($user, 'No user found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $exercise = Exercise::where('id', 1)
            ->where('user_id', $user->id)
            ->first();

        $this->assertNotNull($exercise, 'No exercise found with the given ID for this user');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/exercises/{$exercise->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Exercise deleted successfully',
            ]);


        dd($response->json());
    }

    public function testCreatePredefinedExercise()
    {
        $adminUser = User::where('email', 'admin@gmail.com')->first();
        $this->assertNotNull($adminUser, 'No admin user found with the given email');

        $this->assertTrue($adminUser->isAdmin(), 'The user should be an admin');

        $token = JWTAuth::fromUser($adminUser);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/pre-defined-exercises', [
            'name' => 'Spinal twist',
            'type' => 'Flexibility',
            'description' => 'A Flexibility exercise',
            'instructions' => 'This is the instructions part for the exercise Spinal twist.',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'name',
                'type',
                'description',
                'instructions',
                'user_id',
                'created_at',
                'updated_at',
            ]);

        dd($response->json());
    }

    public function testSearchExercise()
    {
        $user = User::where('email', 'ashikur@gmail.com')->first();
        $this->assertNotNull($user, 'No user found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $exerciseNameToSearch = 'up';

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/exercise/search', [
            'name' => $exerciseNameToSearch,
        ]);


        dd($response->json());
    }

    public function testUpdateOtherUsersExercise()
    {
        $user = User::where('email', 'sajid@gmail.com')->first();
        $this->assertNotNull($user, 'No user found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $otherUserExercise = Exercise::where('id', 1)
            ->whereNot('user_id', $user->id)
            ->first();

        $this->assertNotNull($otherUserExercise, 'No exercise found for another user');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/exercises/{$otherUserExercise->id}", [
            'name' => 'Unauthorized Update',
            'type' => 'Strength',
            'description' => 'An unauthorized update attempt',
        ]);

        $response->assertStatus(403)
            ->assertJson(['error' => 'Unauthorized']);

        dd($response->json());
    }

    public function testDeleteOtherUsersExercise()
    {
        $user = User::where('email', 'sajid@gmail.com')->first();
        $this->assertNotNull($user, 'No user found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $otherUserExercise = Exercise::where('id', 1)
            ->whereNot('user_id', $user->id)
            ->first();

        $this->assertNotNull($otherUserExercise, 'No exercise found for another user');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/exercises/{$otherUserExercise->id}");

        $response->assertStatus(403)
            ->assertJson(['error' => 'Unauthorized']);

        dd($response->json());
    }

    public function testSearchExerciseNoResults()
    {
        $user = User::where('email', 'ashikur@gmail.com')->first();
        $this->assertNotNull($user, 'No user found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $exerciseNameToSearch = 'Exercise';

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/exercise/search', [
            'name' => $exerciseNameToSearch,
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Exercises not found',
            ]);

        dd($response->json());
    }

    public function testCreateExerciseUnknownFields()
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
        ])->postJson('/api/exercises', [
            'name' => 'Bicep',
            'type' => 'abc',
            'description' => 'A Strength exercise',
        ]);

        $response->assertStatus(500)
            ->assertJson([
                'message' => 'The selected type is invalid.',
            ]);

        dd($response->json());
    }

    public function testStoreSameNameExercise()
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
        ])->postJson('/api/exercises',  [
            'name' => 'Bicep curl',
            'type' => 'Strength',
            'description' => 'A Strength exercise',
        ]);

        $response->assertStatus(500)
            ->assertJson([
                'message' => 'The name has already been taken.',
            ]);

        dd($response->json());
    }

    public function testUnauthorizedAccessNoToken()
    {
        $response = $this->getJson('/api/exercises');

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);

        dd($response->json());
    }

    protected function handleInactiveUser($message)
    {
        dd("message: " . $message);
        return response($message, 403);
    }
}
