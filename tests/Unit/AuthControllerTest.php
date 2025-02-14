<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthControllerTest extends TestCase
{
    public function testRegisterUser()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@gmail.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role_id' => 2,
        ]);

        $response->assertStatus(201);

        $response->assertJson([
            'message' => 'User registered successfully',
            'user' => [
                'name' => 'Test User',
                'email' => 'test@gmail.com',
                'role_id' => 2
            ]
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@gmail.com',
        ]);
    }

    public function testSuccessfulLogin()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@gmail.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'token'
            ]);

        $token = $response->json('token');
        $this->assertTrue(JWTAuth::setToken($token)->check());

        dd($response->json('token'));
    }

    public function testUnsuccessfulLogin()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Invalid credentials'
            ]);

        dd($response->json());
    }

    public function testGetAuthenticatedUser()
    {
        $user = User::where('email', 'test@gmail.com')->first();

        $this->assertNotNull($user, 'No user found with the given email');

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role_id' => $user->role_id,
            ]);

        dd($response->json());
    }

    public function testGetUnAuthenticatedUser()
    {
        $validUser = User::where('email', 'testing@gmail.com')->first();

        $this->assertNotNull($validUser, 'No user found with the given email');

        $token = JWTAuth::fromUser($validUser);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/me');

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'User not found'
            ]);

        dd($response->json());
    }


    public function testActivateUserAsAdmin()
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $this->assertTrue($admin->isAdmin(), 'The user should be an admin');

        $user = User::where('email', 'test@gmail.com')->first();
        $this->assertNotNull($user, 'No user found with the given email');

        $token = JWTAuth::fromUser($admin);
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/users/activate/' . $user->id);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'User account activated successfully.'
            ]);

        $user->refresh();

        dd($response->json());
    }

    public function testDeactivateUserAsAdmin()
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $this->assertTrue($admin->isAdmin(), 'The user should be an admin');

        $user = User::where('email', 'test@gmail.com')->first();
        $this->assertNotNull($user, 'No user found with the given email');

        $token = JWTAuth::fromUser($admin);
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/users/deactivate/' . $user->id);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'User account deactivated successfully.'
            ]);

        $user->refresh();

        dd($response->json());
    }

    public function testActivateUserAsNonAdmin()
    {
        $nonAdmin = User::where('email', 'admins@gmail.com')->first();
        $this->assertFalse($nonAdmin->isAdmin(), 'The user should not be an admin');

        $user = User::where('email', 'user@example.com')->first();
        $this->assertNotNull($user, 'No user found with the given email');

        $token = JWTAuth::fromUser($nonAdmin);
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/users/activate/' . $user->id);

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Unauthorized'
            ]);

        dd($response->json());
    }

    public function testDeactivateUserAsNonAdmin()
    {
        $nonAdmin = User::where('email', 'admins@gmail.com')->first();
        $this->assertFalse($nonAdmin->isAdmin(), 'The user should not be an admin');

        $user = User::where('email', 'user@example.com')->first();
        $this->assertNotNull($user, 'No user found with the given email');

        $token = JWTAuth::fromUser($nonAdmin);
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/users/deactivate/' . $user->id);

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Unauthorized'
            ]);
        dd($response->json());
    }


}
