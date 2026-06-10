<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthApiController extends Controller
{
    /**
     * Login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::with([
            'role',
            'business'
        ])->where(
            'email',
            $request->email
        )->first();

        if (
            !$user ||
            !Hash::check(
                $request->password,
                $user->password
            )
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Email au password si sahihi.'
            ], 401);
        }

        // futa token za zamani za mobile
        $user->tokens()->delete();

        $token = $user
            ->createToken('mobile-app')
            ->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'token'   => $token,
            'user'    => $user,
        ]);
    }

    /**
     * Current User
     */
    public function user(Request $request)
    {
        return response()->json([
            'success' => true,
            'user'    => $request->user()->load([
                'role',
                'business'
            ]),
        ]);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $request->user()
            ->currentAccessToken()
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful'
        ]);
    }
}