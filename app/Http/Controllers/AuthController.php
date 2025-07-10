<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'user_type' => 'required|in:student,faculty,super_admin',
            'student_id' => 'required_if:user_type,student|string|max:255',
            'course' => 'required_if:user_type,student|string|max:255',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'student_id' => $request->user_type === 'student' ? $request->student_id : null,
            'course' => $request->user_type === 'student' ? $request->course : null,
        ]);

        // Assign role based on user type
        $user->assignRole($request->user_type);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'roles' => $user->getRoleNames()->toArray(),
            'primary_role' => $user->getRoleNames()->first(),
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'roles' => $user->getRoleNames()->toArray(),
            'primary_role' => $user->getRoleNames()->first(),
            'role' => $user->getRoleNames()->first(), // Legacy compatibility
            'user_roles' => $user->getRoleNames()->toArray(), // Alternative naming
            'group_role' => $user->group_role,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
}
