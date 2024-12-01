<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserService
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function registerUser(array $data)
    {
        return $this->userRepository->createUser($data);
    }

    public function loginUser(array $data): void
    {
        // Find the user by email
        $user = $this->userRepository->getUserByEmail($data['email']);

        // If user is not found, throw a validation exception
        if (!$user) {
            throw ValidationException::withMessages(['email' => 'The email address is not registered.']);
        }

        // If the password is incorrect, throw a validation exception
        if (!Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['password' => 'The password you entered is incorrect.']);
        }

        // Log the user in if credentials are correct
        Auth::login($user);
    }
}
