<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyUserEmail;


class UserController extends Controller
{

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "email" => "required|email|unique:users",
            "password" => "required|min:8|confirmed",
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
            "password" => $request->password, // Model handles hashing via 'hashed' cast
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

        // Email verification check
        if (!$user->email_verified_at) {
            return response()->json([
                "status" => false,
                "message" => "Please verify your email before login."
            ]);
        }

        // Status check
        if ($user->status == 0) {
            return response()->json([
                "status" => false,
                "message" => "Your account has been deactivated by the admin."
            ]);
        }

        Auth::login($user);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            "status" => true,
            "message" => "Login successful",
            "token" => $token,
            "role" => $user->role,
            "data" => $user
        ]);
    }

    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->tokens()->delete();
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully'
        ]);
    }
    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "email" => "required|email|unique:users,email," . $user->id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "errors" => $validator->errors(),
            ]);
        }

        $user->name = trim($request->name);
        $user->email = trim($request->email);
        $user->save();

        return response()->json([
            "status" => true,
            "message" => "Profile Updated Successfully",
            "data" => $user,
        ]);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "errors" => $validator->errors(),
            ]);
        }

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                "status" => false,
                "errors" => ["current_password" => ["The current password does not match our records."]],
            ]);
        }

        $user->password = $request->new_password;
        $user->save();

        return response()->json([
            "status" => true,
            "message" => "Password Changed Successfully",
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
