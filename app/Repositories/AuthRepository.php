<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthRepository
{
    public function findUserById($id)
    {
        return User::findOrFail($id);
    }

    public function createUser($data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => $data['role_id'],
        ]);
    }

    public function generateToken($user)
    {
        return JWTAuth::fromUser($user);
    }

    public function activateUser($user)
    {
        $user->active = true;
        $user->save();
    }

    public function deactivateUser($user)
    {
        $user->active = false;
        $user->save();
    }
}
