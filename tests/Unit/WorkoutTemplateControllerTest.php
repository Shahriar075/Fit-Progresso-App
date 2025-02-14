<?php

namespace Tests\Unit;

use App\Models\Exercise;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkoutTemplate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class WorkoutTemplateControllerTest extends TestCase
{

    public function testAdminCanViewAllTemplates()
    {
        $adminUser = User::where('email', 'admin@gmail.com')->first();
        $this->assertNotNull($adminUser, 'No admin user found with the given email');

        $this->assertTrue($adminUser->isAdmin(), 'The user should be an admin');

        $token = JWTAuth::fromUser($adminUser);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/workout-templates');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => ['id', 'name', 'description', 'created_by', 'exercises']
            ]);

        dd($response->json());
    }

    public function testGeneralUserCanViewOwnAndAdminTemplates()
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
        ])->getJson('/api/workout-templates');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => ['id', 'name', 'description', 'created_by', 'exercises']
            ]);

        dd($response->json());
    }

    public function testWithoutTemplatesFound()
    {
        $user = User::where('email', 'sajid@gmail.com')->first();
        $this->assertNotNull($user, 'No user found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/workout-templates');

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'No templates found.'
            ]);

        dd($response->json());
    }

    public function testCreateWorkoutTemplateWithValidExercises()
    {
        $user = User::where('email', 'sajid@gmail.com')->first();
        $this->assertNotNull($user, 'User not found');

        $token = JWTAuth::fromUser($user);

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $exerciseIds = [9, 15];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/workout-templates', [
            'name' => 'Full Body Workout by Sajid',
            'description' => 'A comprehensive workout template by Sajid',
            'exercise_ids' => $exerciseIds,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'template' => [
                    'id',
                    'name',
                    'description',
                    'created_by',
                ],
            ])
            ->assertJson([
                'message' => 'Workout template created successfully',
            ]);

        dd($response->json());
    }

    public function testCreateWorkoutTemplateWithInvalidExercises()
    {
        $user = User::where('email', 'sajid@gmail.com')->first();
        $this->assertNotNull($user, 'User not found');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $exerciseIds = [1, 9];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/workout-templates', [
            'name' => 'Workout Template by Sajid',
            'description' => 'This is with invalid or unauthorized exercises',
            'exercise_ids' => $exerciseIds,
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'Unauthorized exercise inclusion',
            ]);

        dd($response->json());
    }

    public function testUpdateWorkoutTemplateWithValidData()
    {
        $user = User::where('email', 'sajid@gmail.com')->first();
        $this->assertNotNull($user, 'User not found');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $template = WorkoutTemplate::where('created_by', $user->id)->first();
        $this->assertNotNull($template, 'Workout template not found');

        $exerciseIds = [9, 15];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/workout-templates/{$template->id}", [
            'name' => 'Updated Full Body Workout',
            'description' => 'Updated description for workout template by Sajid',
            'exercise_ids' => $exerciseIds,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'template' => [
                    'id',
                    'name',
                    'description',
                    'created_by',
                ],
            ])
            ->assertJson([
                'message' => 'Workout template updated successfully',
            ]);

        dd($response->json());
    }

    public function testUpdateWorkoutTemplateWithInvalidExerciseIds()
    {
        $user = User::where('email', 'sajid@gmail.com')->first();
        $this->assertNotNull($user, 'User not found');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $template = WorkoutTemplate::where('created_by', $user->id)->first();
        $this->assertNotNull($template, 'Workout template not found');

        $exerciseIds = [9, 1];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/workout-templates/{$template->id}", [
            'name' => 'Updated Workout',
            'description' => 'Description with invalid exercise IDs',
            'exercise_ids' => $exerciseIds,
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'Unauthorized exercise inclusion',
            ]);

        dd($response->json());
    }

    public function testDestroyWorkoutTemplateWithValidData()
    {
        $user = User::where('email', 'sajid@gmail.com')->first();
        $this->assertNotNull($user, 'User not found');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $template = WorkoutTemplate::where('created_by', $user->id)->first();
        $this->assertNotNull($template, 'Workout template not found');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/workout-templates/{$template->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Workout template deleted successfully',
            ]);

        dd($response->json());
    }

    public function testUnauthorizedWorkoutTemplateDeletion()
    {
        $user = User::where('email', 'sajid@gmail.com')->first();
        $this->assertNotNull($user, 'User not found');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $template = WorkoutTemplate::whereNot('created_by', $user->id)->first();
        $this->assertNotNull($template, 'Another user’s workout template not found');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/workout-templates/{$template->id}");

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'Unauthorized',
            ]);

        dd($response->json());
    }

    public function testDestroyWorkoutTemplateWithoutAuthorization()
    {
        $template = WorkoutTemplate::first();
        $this->assertNotNull($template, 'Workout template not found');

        $response = $this->getJson("/api/workout-templates/{$template->id}");

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);

        dd($response->json());
    }

    public function testWorkoutTemplateWithoutAuthorization()
    {
        $response = $this->getJson("/api/workout-templates");

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