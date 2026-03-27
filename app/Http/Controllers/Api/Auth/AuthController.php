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
            ], 422);
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
            "message" => "Registration successful. Please verify email.",
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
            ], 422);
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
                "message" => "Account not verified or blocked",
            ], 401);
        }

        if ($user->is_first_login) {

            $otp = random_int(100000, 999999);
            $otpToken = Str::uuid();

            $user->update([
                'otp' => Hash::make($otp),
                'otp_token' => $otpToken,
                'otp_expires_at' => now()->addMinutes(5),
                'otp_attempts' => 0,
                'otp_last_sent_at' => now()
            ]);

            session(['otp_token' => $otpToken]);

            // Send OTP
            Mail::raw("Your OTP is: $otp", function ($message) use ($user) {
                $message->to($user->email)->subject('OTP Verification');
            });

            return response()->json([
                "status" => true,
                "message" => "OTP sent to your email",
                "otp_token" => $otpToken
            ]);
        }
        Auth::login($user);
        $user->tokens()->delete();

        $token = $user->createToken($request->userAgent(), ['*'], now()->addHours(2))->plainTextToken;

        return response()->json([
            "status" => true,
            "message" => "Login successful",
            "token" => $token,
            "role" => $user->role,
            "data" => $user
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->merge([
            'otp_token' => $request->input('otp_token') ?: $request->input('otpToken') ?: session('otp_token')
        ]);

        $validator = Validator::make($request->all(), [
            'otp' => 'required|digits:6',
            'otp_token' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "errors" => $validator->errors(),
            ], 422);
        }

        $user = User::where('otp_token', $request->otp_token)->first();
        if (!$user) {
            return response()->json([
                "status" => false,
                "message" => "Invalid token"
            ], 404);
        }

        if (now()->gt($user->otp_expires_at)) {
            return response()->json([
                "status" => false,
                "message" => "OTP expired"
            ], 401);
        }

        if ($user->otp_attempts >= 5) {
            return response()->json([
                "status" => false,
                "message" => "Too many attempts"
            ], 429);
        }

        // OTP match
        if (!Hash::check($request->otp, $user->otp)) {
            $user->increment('otp_attempts');

            return response()->json([
                "status" => false,
                "message" => "Invalid OTP"
            ], 401);
        }

        // SUCCESS
        $user->update([
            'otp' => null,
            'otp_token' => null,
            'otp_expires_at' => null,
            'otp_attempts' => 0,
            'is_first_login' => false
        ]);

        session()->forget('otp_token');
        Auth::login($user);
        $user->tokens()->delete();

        $token = $user->createToken(request()->userAgent(), ['*'], now()->addHours(2))->plainTextToken;

        return response()->json([
            "status" => true,
            "message" => "Login successful",
            "token" => $token,
            "role" => $user->role,
            "data" => $user
        ]);
    }
    public function resendOtp(Request $request)
    {

        $request->merge(['otp_token' => $request->input('otp_token') ?: $request->input('otpToken') ?: session('otp_token')]);

        $validator = Validator::make($request->all(), [
            'otp_token' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "errors" => $validator->errors(),
            ], 422);
        }

        $user = User::where('otp_token', $request->otp_token)->first();

        if (!$user) {
            return response()->json([
                "status" => false,
                "message" => "Invalid token"
            ], 404);
        }

        // Rate limit (60 sec)
        if ($user->otp_last_sent_at && now()->diffInSeconds($user->otp_last_sent_at) < 60) {
            return response()->json([
                "status" => false,
                "message" => "Wait before requesting OTP again"
            ], 429);
        }

        $otp = random_int(100000, 999999);

        $user->update([
            'otp' => Hash::make($otp),
            'otp_expires_at' => now()->addMinutes(5),
            'otp_last_sent_at' => now()
        ]);

        Mail::raw("Your new OTP is: $otp", function ($message) use ($user) {
            $message->to($user->email)->subject('Resend OTP');
        });

        return response()->json([
            "status" => true,
            "message" => "OTP resent successfully"
        ]);
    }

    // ================= LOGOUT =================
    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->tokens()->delete();
        }

        Auth::logout();

        return response()->json([
            "status" => true,
            "message" => "Logged out successfully"
        ]);
    }

    // ================= REFRESH TOKEN =================
    public function refreshToken(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                "status" => false,
                "message" => "Unauthorized"
            ], 401);
        }

        $user->tokens()->delete();

        $token = $user->createToken($request->userAgent(), ['*'], now()->addHours(2))->plainTextToken;

        return response()->json([
            "status" => true,
            "token" => $token
        ]);
    }

    public function activateAccount($token)
    {
        $user = User::where('verification_token', $token)->first();

        if (!$user) {
            return response()->json([
                "status" => false,
                "message" => "Invalid link"
            ]);
        }

        if ($user->email_verified_at) {
            return response()->json([
                "status" => true,
                "message" => "Already verified"
            ]);
        }

        $user->update([
            'email_verified_at' => now(),
            'verification_token' => null
        ]);

        return response()->json([
            "status" => true,
            "message" => "Account activated"
        ]);
    }
}