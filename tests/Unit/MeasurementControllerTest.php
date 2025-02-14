<?php

namespace Tests\Unit;

use App\Models\Exercise;
use App\Models\MeasureType;
use App\Models\Role;
use App\Models\User;
use App\Models\UserMeasure;
use App\Models\WorkoutLog;
use App\Models\WorkoutTemplate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class MeasurementControllerTest extends TestCase
{

    public function testadminCanCreateMeasurementTypeWithValidName()
    {
        $user = User::where('email', 'admin@gmail.com')->first();
        $this->assertNotNull($user, 'User not found with the given email');

        $this->assertTrue($user->isAdmin(), 'The user should be an admin');

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' =>  'Bearer ' . $token
        ])->postJson('/api/measure-types', [
            'name' => 'Caloric intake'
        ]);

        $response->assertStatus(201);

        dd($response->json());

    }

    public function testadminCanNotCreateMeasurementTypeWithSameName()
    {
        $user = User::where('email', 'admin@gmail.com')->first();
        $this->assertNotNull($user, 'User not found with the given email');

        $this->assertTrue($user->isAdmin(), 'The user should be an admin');

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' =>  'Bearer ' . $token
        ])->postJson('/api/measure-types', [
            'name' => 'Caloric intake'
        ]);

        $response->assertStatus(400);

        dd($response->json());

    }

    public function testUnauthorizedUserCanNotCreateMeasurementType()
    {
        $user = User::where('email', 'ashikur@gmail.com')->first();
        $this->assertNotNull($user, 'User not found with the given email');

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' =>  'Bearer ' . $token
        ])->postJson('/api/measure-types', [
            'name' => 'Calories'
        ]);

        $response->assertStatus(401);

        dd($response->json());

    }

    public function testUserCanInputMeasurementWithValidMeasurementType()
    {
        $user = User::where('email', 'ashikur@gmail.com')->first();
        $this->assertNotNull($user, 'User not found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $date = new \DateTime();

        $response = $this->withHeaders([
            'Authorization' =>  'Bearer ' . $token
        ])->postJson('/api/measures/input', [
            'user_id' => $user->id,
            'measure_type_id' => 1,
            'value' => 65,
            'recorded_on' => $date,
        ]);

        $response->assertStatus(201);

        dd($response->json());
    }

    public function testUserCanInputMeasurementWithInvalidMeasurementType()
    {
        $user = User::where('email', 'ashikur@gmail.com')->first();
        $this->assertNotNull($user, 'User not found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $date = new \DateTime();

        $response = $this->withHeaders([
            'Authorization' =>  'Bearer ' . $token
        ])->postJson('/api/measures/input', [
            'user_id' => $user->id,
            'measure_type_id' => 4,
            'value' => 60,
            'recorded_on' => $date,
        ]);

        $response->assertStatus(422);

        dd($response->json());
    }

    public function testUserCanInputMeasurementWithNoMeasurementType()
    {
        $user = User::where('email', 'ashikur@gmail.com')->first();
        $this->assertNotNull($user, 'User not found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $date = new \DateTime();

        $response = $this->withHeaders([
            'Authorization' =>  'Bearer ' . $token
        ])->postJson('/api/measures/input', [
            'user_id' => $user->id,
            'measure_type_id' => '',
            'value' => 60,
            'recorded_on' => $date,
        ]);

        $response->assertStatus(422);

        dd($response->json());
    }

    public function testUserCanUpdateMeasurementWithValidMeasurementType()
    {
        $user = User::where('email', 'ashikur@gmail.com')->first();
        $this->assertNotNull($user, 'User not found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $date = new \DateTime();

        $measurement = UserMeasure::where('user_id', $user->id)
            ->where('measure_type_id', 1)
            ->first();

        $response = $this->withHeaders([
            'Authorization' =>  'Bearer ' . $token
        ])->postJson("/api/measures/{$measurement->id}", [
            'measure_type_id' => 1,
            'value' => 62,
            'recorded_on' => $date,
        ]);

        $response->assertStatus(201);

        dd($response->json());
    }

    public function testUserCanUpdateMeasurementWithInvalidMeasurementType()
    {
        $user = User::where('email', 'ashikur@gmail.com')->first();
        $this->assertNotNull($user, 'User not found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $date = new \DateTime();

        $measurement = UserMeasure::where('user_id', $user->id)
            ->where('measure_type_id', 4)
            ->first();

        $this->assertNotNull($measurement, 'Measurement not found');

        $response = $this->withHeaders([
            'Authorization' =>  'Bearer ' . $token
        ])->postJson("/api/measures/{$measurement->id}", [
            'measure_type_id' => 4,
            'value' => 62,
            'recorded_on' => $date,
        ]);

        $response->assertStatus(422);

        dd($response->json());
    }

    public function testDeleteMeasurementWithValidMeasurementType()
    {
        $user = User::where('email', 'ashikur@gmail.com')->first();
        $this->assertNotNull($user, 'User not found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $measurement = UserMeasure::where('user_id', $user->id)
            ->where('measure_type_id', 1)
            ->first();

        $response = $this->withHeaders([
            'Authorization' =>  'Bearer ' . $token
        ])->getJson("/api/measures/{$measurement->id}");

        $response->assertStatus(200);

        dd($response->json());
    }

    public function testDeleteMeasurementWithInvalidMeasurementType()
    {
        $user = User::where('email', 'ashikur@gmail.com')->first();
        $this->assertNotNull($user, 'User not found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $measurementId = 10;

        $response = $this->withHeaders([
            'Authorization' =>  'Bearer ' . $token
        ])->getJson("/api/measures/{$measurementId}");

        $response->assertStatus(404);

        dd($response->json());
    }

    public function testDeleteMeasurementHistoryWithoutAuthorization()
    {
        $measurementId = 10;

        $response = $this->getJson("/api/measures/{$measurementId}");

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);

        dd($response->json());
    }

    public function testGetMeasurementHistoryWithValidMeasurementType()
    {
        $user = User::where('email', 'ashikur@gmail.com')->first();
        $this->assertNotNull($user, 'User not found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $measureTypeId = 1;

        $response = $this->withHeaders([
            'Authorization' =>  'Bearer ' . $token
        ])->getJson("/api/measures/history/{$measureTypeId}");


        $response->assertStatus(201);

        dd($response->json());
    }

    public function testGetMeasurementHistoryWithInvalidMeasurementType()
    {
        $user = User::where('email', 'ashikur@gmail.com')->first();
        $this->assertNotNull($user, 'User not found with the given email');

        try {
            $user->checkActive();
        } catch (\Exception $e) {
            return $this->handleInactiveUser($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        $measureTypeId = 3;

        $response = $this->withHeaders([
            'Authorization' =>  'Bearer ' . $token
        ])->getJson("/api/measures/{$measureTypeId}");


        $response->assertStatus(404);

        dd($response->json());
    }

    public function testGetMeasurementHistoryWithoutAuthorization()
    {
        $measureTypeId = 3;

        $response = $this->getJson("/api/measures/history/{$measureTypeId}");

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);

        dd($response->json());
    }

    public function testGetAllMeasureTypes()
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
            'Authorization' =>  'Bearer ' . $token
        ])->getJson('/api/measure-types/history');

        $response->assertStatus(200);

        dd($response->json());
    }

    protected function handleInactiveUser($message)
    {
        dd("message: " . $message);
        return response($message, 403);
    }

}