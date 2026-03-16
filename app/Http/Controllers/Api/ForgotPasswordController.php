<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        $validate = Validator::make($request->all(), [
            "email" => "required|email",
        ]);

        if ($validate->fails()) {
            return response()->json([
                "status" => false,
                "errors" => $validate->errors(),
            ]);
        }
        $email = $request->email;
        $user = User::where("email", $email)->first();

        if (!$user) {
            return response()->json([
                "status" => false,
                "message" => "User not found",
            ]);
        }
        $status = Password::sendResetLink([
            "email" => $email,
        ]);

        if ($status == Password::RESET_LINK_SENT) {
            return response()->json([
                "status" => true,
                "message" => "Password reset link sent successfully",
            ]);
        }
        return response()->json([
            "status" => false,
            "message" => "Unable to send password reset link",
        ]);
    }

    public function reset(Request $request)
    {
        $validate = Validator::make($request->all(), [
            "token" => "required",
            "email" => "required|email",
            "password" => "required|min:8|confirmed"
        ]);

        if ($validate->fails()) {
            return response()->json([
                "status" => false,
                "errors" => $validate->errors(),
            ]);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                "status" => true,
                "message" => "Password reset successfully",
            ]);
        }

        return response()->json([
            "status" => false,
            "message" => __($status),
        ]);
    }
}
