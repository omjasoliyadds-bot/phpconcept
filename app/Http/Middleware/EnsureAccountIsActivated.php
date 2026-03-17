<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Auth;

class EnsureAccountIsActivated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            if (!$request->user()->email_verified_at) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Your account is not activated. Please check your email for the activation link.'
                    ], 403);
                }

                Auth::logout();
                return redirect()->route('login')->with('error', 'Please activate your account first.');
            }

            if ($request->user()->status == 0) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Your account has been deactivated by the administrator.'
                    ], 403);
                }

                Auth::logout();
                return redirect()->route('login')->with('error', 'Your account has been deactivated.');
            }
        }

        return $next($request);
    }
}
