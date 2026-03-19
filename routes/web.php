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
    Route::get('users', [AdminController::class, 'usersView'])->name('admin.users.view');
    Route::get('profile', [AdminController::class, 'profile'])->name('admin.profile');
    Route::get('document', [AdminController::class, 'documentsView'])->name('admin.documents.view');
    Route::get('/documents/{id}/manage-access', [AdminController::class, 'manageAccess'])->name('admin.documents.manage-access');
});

// User View Routes
Route::middleware(['auth', 'activated'])->group(function () {
    Route::get('dashboard', [UserController::class, 'userDashboardView'])->name('user.dashboard');
    Route::get('profile', [UserController::class, 'userProfile'])->name('user.profile');
    Route::get('/folders', [UserController::class, 'userFolders'])->name('folders.index');
    Route::get('/folders/{id}/files', [UserController::class, 'folderFiles'])->name('folders.show');
    Route::get('/explorer', [UserController::class, 'explorerView'])->name('explorer.index');
    Route::get('share', [UserController::class,'shareWithMeView'])->name('user.share-with-me');
    Route::get('/documents/{id}/manage-access', [UserController::class, 'manageAccess'])->name('documents.manage-access');
    Route::get('/documents/{id}/download', [App\Http\Controllers\Api\DocumentController::class, 'download'])->name('documents.download');
});