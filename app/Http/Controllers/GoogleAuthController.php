<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callbackGoogle()
    {
        try {
            $user = Socialite::driver('google')->user();

            $users = User::create([
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'password' => 'password',
                'role_id' => 2,
            ]);
            Auth::login($users);
            return response()->json(['user' => $user]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }
}
