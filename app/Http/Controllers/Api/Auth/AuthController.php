<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyUserEmail;

class AuthController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "email" => "required|email|unique:users",
            "password" => "required|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/",
        ], [
            'password.regex' => 'The password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&).'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "errors" => $validator->errors(),
            ]);
        }

        $token = Str::random(64);

        $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => Hash::make($request->password),
            "verification_token" => $token
        ]);

        $link = route('activate.account', $token);
        Mail::to($user->email)->send(new VerifyUserEmail($link));

        return response()->json([
            "status" => true,
            "message" => "Registration successful. Please check your email to activate your account.",
            "data" => $user
        ]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "email" => "required|email",
            "password" => "required",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "errors" => $validator->errors(),
            ]);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                "status" => false,
                "message" => "Invalid Credentials",
            ], 401);
        }

        if (!$user->email_verified_at || $user->status == 0) {
            return response()->json([
                "status" => false,
                "message" => "Invalid Credentials",
            ], 401);
        }

        Auth::login($user);
        $request->session()->regenerate();
        $user->tokens()->delete();
        $token = $user->createToken($request->userAgent(), ['*'], now()->addHours(2))->plainTextToken;
        auditLog('Login', 'Auth', 'User logged in successfully', null, null, $user->id, $user->id);

        return response()->json([
            "status" => true,
            "message" => "Login successful",
            "token" => $token,
            "role" => $user->role,
            "data" => [
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
                "role" => $user->role
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if ($request->user()) {
            auditLog('Logout', 'Auth', 'User logged out', null, null, $request->user()->id, $request->user()->id);
            // Revoke all tokens for session integrity
            $user->tokens()->delete();
        }

        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    public function refreshToken(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->currentAccessToken()) {
            return response()->json([
                'status' => false,
                'message' => 'No active token found. Please login again.'
            ], 401);
        }

        $user->tokens()->delete();

        $newToken = $user->createToken($request->userAgent(), ['*'], now()->addHours(2))->plainTextToken;
        auditLog('Token Refresh', 'Auth', 'User refreshed API token', null, null, $user->id, $user->id);

        return response()->json([
            'status' => true,
            'message' => 'Token refreshed successfully',
            'token' => $newToken
        ]);
    }

    public function activateAccount($token)
    {
        $user = User::where('verification_token', $token)->first();

        if (!$user) {
            return response()->json([
                "status" => false,
                "message" => "Invalid or expired activation link"
            ]);
        }

        if ($user->email_verified_at) {
            return response()->json([
                "status" => true,
                "message" => "Account already verified"
            ]);
        }

        $user->email_verified_at = now();
        $user->verification_token = null;
        $user->save();

        return response()->json([
            "status" => true,
            "message" => "Account Activated Successfully"
        ]);
    }
}
