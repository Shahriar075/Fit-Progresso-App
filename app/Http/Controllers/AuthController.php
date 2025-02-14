<?php

namespace App\Http\Controllers;

use App\Models\Role;
use GuzzleHttp\Middleware;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
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

        $role = Role::find($validatedData['role_id']);
        if (!$role) {
            return response()->json(['error' => 'Invalid role'], 400);
        }

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role_id' => $validatedData['role_id'],
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
        ], 201);
    }

    public function me()
    {
        return response()->json(auth()->user());
    }

    public function activateUser($id)
    {
        $authUser= Auth::user();

        $user = User::findOrFail($id);

        if($authUser != $authUser->isAdmin()){
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $user->active = true;
        $user->save();

        return response()->json(['message' => 'User account activated successfully.']);
    }

    public function deactivateUser($id)
    {
        $authUser= Auth::user();

        $user = User::findOrFail($id);

        if($authUser != $authUser->isAdmin()){
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $user->active = false;
        $user->save();

        return response()->json(['message' => 'User account deactivated successfully.']);
    }

}
