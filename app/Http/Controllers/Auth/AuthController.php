<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function loginView()
    {
        return view('login');
    }

    public function showLinkRequestForm(Request $request, $token = null)
    {
        return view('user.auth.forgot-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function showResetPasswordForm(Request $request, $token = null)
    {
        return view('user.auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }
}
