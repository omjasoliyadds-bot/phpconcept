<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by(($request->email ?? 'guest') . $request->ip() . $request->userAgent())
                ->response(function () {
                    return response()->json([
                        'status' => false,
                        'message' => 'Too many login attempts. Please try again later.'
                    ], 429);
                })
            ;
        });

        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'status' => false,
                        'message' => 'Too many registration attempts. Please try again later.'
                    ], 429);
                });
        });

        RateLimiter::for('password', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'status' => false,
                        'message' => 'Too many password reset attempts. Please try again later.'
                    ], 429);
                });
        });

        RateLimiter::for('otp-attempts', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'status' => false,
                        'message' => 'Too many OTP attempts. Please try again later.'
                    ], 429);
                });
        });
    }
}