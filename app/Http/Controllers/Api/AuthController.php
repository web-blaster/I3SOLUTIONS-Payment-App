<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // Login
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            $credentials = $request->only('email', 'password');

            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'error' => 'Invalid credentials'
                ], 401);
            }

            // Get the authenticated user
            $user = JWTAuth::user(); // returns User model

            // Get the TTL from JWTAuth
            $ttl = JWTAuth::factory()->getTTL() * 60; // seconds

            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => $ttl,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    // add more fields if needed
                ]
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Login failed', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'error' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }


    // Get Authenticated User
    public function me(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json([
                    'error' => 'User not authenticated'
                ], 401);
            }

            return response()->json($user, 200);
        } catch (\Throwable $th) {
            Log::error('Fetching authenticated user failed', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Something went wrong.'
            ], 500);
        }
    }

    // Logout
    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'message' => 'Successfully logged out'
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Logout failed', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Something went wrong.'
            ], 500);
        }
    }
}
