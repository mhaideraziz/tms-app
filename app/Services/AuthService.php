<?php

namespace App\Services;

use App\Repositories\AuthRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    protected $repository;
    public function __construct(AuthRepository $repository)
    {
        $this->repository = $repository;
    }

    public function login($email, $password)
    {
        try {
        // Retrieve the user by mobile
        $user = $this->repository->login($email);

        // Check if user exists and verify the password
        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Generate the authentication token
        $token = $user->createToken('auth-token')->plainTextToken;

        if ($token) {
            $token = 'Bearer ' . $token;
            return response()->json(['token' => $token, 'user' => $user], 200);
        } else {
            return response()->json(['message' => 'Failed to generate authentication token'], 500);
        }
        } catch (\Exception $e) {
//             Handle unexpected errors
            return response()->json(['error' => 'An error occurred while processing the login'], 500);
        }
    }


    public function logout(Request $request)
    {
        return $request->user()->currentAccessToken()->delete();
    }
    /**
     * Create user function
     */
    public function createUser($name, $email, $password)
    {
        try {
            // Create the user
            $user = $this->repository->createUser($name, $email, $password);

            if ($user) {
                return response()->json(['message' => 'User created successfully', 'user' => $user], 201);
            } else {
                return response()->json(['error' => 'Failed to create user'], 500);
            }
        } catch (\Exception $e) {
            // Handle repository or other exceptions
            return response()->json(['error' => 'An unexpected error occurred'], 500);
        }
    }


}
