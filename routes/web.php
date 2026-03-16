<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\DocumentController as ApiDocumentController;
use App\Http\Controllers\Auth\AuthController;

// Public Routes (Only for guests)
Route::middleware(['guest'])->group(function () {
    Route::controller(UserController::class)->group(function () {
        Route::get('/', function () {
            return view('index');
        });
        Route::get('login', 'loginView')->name('login');
    });
    Route::get('forgot-password',[AuthController::class,'showLinkRequestForm'])->name('auth.reset-password');
    Route::get('reset-password/{token}',[AuthController::class,'showResetPasswordForm'])->name('password.reset');
});

// Protected Routes
Route::middleware(['auth', 'activated'])->group(function () {
    Route::controller(UserController::class)->group(function () {
        Route::get('dashboard', 'userDashboardView')->name('user.dashboard');
        Route::get('profile', 'userProfile')->name('user.profile');
    });

    // Admin Routes
    Route::middleware(['admin'])->prefix('admin')->group(function () {
        Route::get('dashboard', [App\Http\Controllers\Admin\AdminController::class, 'index'])->name('admin.dashboard');
    });

    Route::prefix('documents')->group(function () {
        Route::post('upload', [ApiDocumentController::class, 'store'])->name('documents.upload');
        Route::put('{id}', [ApiDocumentController::class, 'update'])->name('documents.update');
        Route::delete('{id}', [ApiDocumentController::class, 'destroy'])->name('documents.destroy');
        Route::get('{id}/download', [ApiDocumentController::class, 'download'])->name('documents.download');
    });

    Route::get('/folders', [UserController::class, 'userFolders'])->name('folders.index');
    Route::get('/folders/{id}/files', [UserController::class, 'folderFiles'])->name('folders.show');
    Route::get('/explorer', [UserController::class, 'explorerView'])->name('explorer.index');
});