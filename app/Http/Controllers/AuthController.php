<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $token = $this->authService->login($credentials);

        if (!$token) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        return response()->json(['token' => $token]);
    }

    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed|min:8',
            'role_id' => 'required|exists:roles,id',
        ]);

        $token = $this->authService->registerUser($validatedData);

        return response()->json([
            'message' => 'User registered successfully',
            'token' => $token
        ], 201);
    }

    public function activateUser($id)
    {
        if (!$this->authService->activateUser($id)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json(['message' => 'User account activated successfully.']);
    }

    public function deactivateUser($id)
    {
        if (!$this->authService->deactivateUser($id)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json(['message' => 'User account deactivated successfully.']);
    }
}
