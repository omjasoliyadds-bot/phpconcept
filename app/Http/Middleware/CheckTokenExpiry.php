<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;

class CheckTokenExpiry
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && $user->currentAccessToken()) {
            $token = $user->currentAccessToken();
            if ($token instanceof PersonalAccessToken) {
                if ($token->expires_at && $token->expires_at->lt(now())) {
                    $token->delete();
                    return response()->json([
                        'status' => false,
                        'message' => 'Your token has expired. Please login again.'
                    ], 401);
                }
            }
        }
        return $next($request);
    }
}
