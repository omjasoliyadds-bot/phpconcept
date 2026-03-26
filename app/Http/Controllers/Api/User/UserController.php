<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
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
            'new_password' => 'required|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/',
        ], [
            'new_password.regex' => 'The password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&).'
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
}
