<?php

use App\Http\Controllers\Admin\AuditLogController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\DocumentController as AdminDocumentController;
use App\Http\Controllers\User\UserController;

// Public View Routes (Guest)
Route::middleware(['guest'])->group(function () {
    Route::get('/', function () {
        return view('index');
    });

    Route::get('login', [AuthController::class, 'loginView'])->name('login');
    Route::get('forgot-password', [AuthController::class, 'showLinkRequestForm'])->name('auth.reset-password');
    Route::get('reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
});

// Admin View Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('users', [AdminUserController::class, 'index'])->name('admin.users.view');
    Route::get('profile', [AdminUserController::class, 'profile'])->name('admin.profile');
    Route::get('document', [AdminDocumentController::class, 'index'])->name('admin.documents.view');
    Route::get('documents/{id}/manage-access', [AdminDocumentController::class, 'manageAccess'])->name('admin.documents.manage-access');
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('admin.audit-logs');
});

// User View Routes
Route::middleware(['auth', 'activated'])->group(function () {
    Route::get('dashboard', [UserController::class, 'dashboard'])->name('user.dashboard');
    Route::get('profile', [UserController::class, 'profile'])->name('user.profile');
    Route::get('/folders', [UserController::class, 'folders'])->name('folders.index');
    Route::get('/folders/{folder}/files', [UserController::class, 'folderFiles'])->name('folders.show');
    Route::get('/explorer', [UserController::class, 'explorer'])->name('explorer.index');
    Route::get('share', [UserController::class, 'sharedWithMe'])->name('user.share-with-me');
    Route::get('/documents/{id}/manage-access', [UserController::class, 'manageAccess'])->name('documents.manage-access');
    Route::get('/documents/{id}/download', [App\Http\Controllers\Api\User\DocumentController::class, 'download'])->name('documents.download');
});