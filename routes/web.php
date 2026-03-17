<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\AdminController;

// Public View Routes (Guest)
Route::middleware(['guest'])->group(function () {
    Route::get('/', function () {
        return view('index');
    });
    
    Route::get('login', [UserController::class, 'loginView'])->name('login');
    Route::get('forgot-password', [AuthController::class, 'showLinkRequestForm'])->name('auth.reset-password');
    Route::get('reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
});

// Admin View Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
});

// User View Routes
Route::middleware(['auth', 'activated'])->group(function () {
    Route::get('dashboard', [UserController::class, 'userDashboardView'])->name('user.dashboard');
    Route::get('profile', [UserController::class, 'userProfile'])->name('user.profile');
    
    Route::get('/folders', [UserController::class, 'userFolders'])->name('folders.index');
    Route::get('/folders/{id}/files', [UserController::class, 'folderFiles'])->name('folders.show');
    Route::get('/explorer', [UserController::class, 'explorerView'])->name('explorer.index');
});