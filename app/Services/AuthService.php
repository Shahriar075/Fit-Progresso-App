<?php

namespace App\Services;

use App\Repositories\AuthRepository;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    protected $authRepository;

    public function __construct(AuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    public function login($credentials)
    {
        if (!$token = JWTAuth::attempt($credentials)) {
            return false;
        }

        return $token;
    }

    public function registerUser($data)
    {
        $user = $this->authRepository->createUser($data);
        return $this->authRepository->generateToken($user);
    }

    public function activateUser($id)
    {
        $authUser = Auth::user();
        $user = $this->authRepository->findUserById($id);

        if (!$authUser->isAdmin()) {
            return false;
        }

        $this->authRepository->activateUser($user);
        return true;
    }

    public function deactivateUser($id)
    {
        $authUser = Auth::user();
        $user = $this->authRepository->findUserById($id);

        if (!$authUser->isAdmin()) {
            return false;
        }

        $this->authRepository->deactivateUser($user);
        return true;
    }
}
