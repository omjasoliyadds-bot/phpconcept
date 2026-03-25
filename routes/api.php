<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;

use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\User\FolderController;
use App\Http\Controllers\Api\User\DocumentController;

use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\Admin\DocumentController as AdminDocumentController;
use App\Http\Controllers\Api\Admin\AuditLogController as AdminAuditLogController;

// Public API Routes (Throttled)
Route::post('register', [AuthController::class, 'store'])->middleware('throttle:register')->name('api.user.store');
Route::post('login', [AuthController::class, 'login'])->middleware('throttle:login')->name('api.login.user');
Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->middleware('throttle:password')->name('password.email');
Route::post('reset', [ForgotPasswordController::class, 'reset'])->middleware('throttle:password')->name('api.password.reset');
Route::get('activate-account/{token}', [AuthController::class, 'activateAccount'])->name('activate.account');

// Protected API Routes 
Route::middleware(['auth:sanctum'])->group(function () {
    // Admin Only API Routes
    Route::middleware(['admin'])->prefix('admin')->group(function () {
        Route::post('/logout', [AdminController::class, 'logout'])->name('api.admin.logout');
        Route::get('/users-data', [AdminUserController::class, 'getUsers'])->name('admin.users.data');
        Route::post('/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('admin.users.toggle');
        Route::post('/toggle-sharing', [AdminUserController::class, 'toggleSharing'])->name('admin.users.toggle_sharing');
        Route::post('/update-storage-limit', [AdminUserController::class, 'updateStorageLimit'])->name('admin.users.update_storage_limit');
        Route::post('profile/update/{id}', [AdminUserController::class, 'updateProfile'])->name('admin.profile.update');
        Route::post('password/update', [AdminUserController::class, 'updatePassword'])->name('admin.password.update');
        Route::get('/documents-data', [AdminDocumentController::class, 'getAllDocuments'])->name('admin.documents.data');
        Route::get('/audit-logs-data', [AdminAuditLogController::class, 'getLogs'])->name('admin.audit-logs.data');
        Route::get('/documents/{id}/permissions', [AdminDocumentController::class, 'getPermissions'])->name('admin.documents.permissions');
        Route::post('/documents/{id}/share', [AdminDocumentController::class, 'share'])->name('admin.documents.share');
        Route::post('/documents/{id}/revoke-permission', [AdminDocumentController::class, 'revokePermission'])->name('admin.documents.revoke_permission');
        Route::post('/revoke-permissions/{id}', [AdminDocumentController::class, 'revokeDocumentPermissions'])->name('admin.documents.revoke');
        Route::delete('/document/{id}/delete', [AdminDocumentController::class, 'forcedDeleteDocument'])->name('admin.documents.forced.delete');
    });

    // Activated User API Routes
    Route::middleware(['activated'])->group(function () {
        Route::get('user', function (Request $request) {
            return $request->user();
        })->name('api.user');

        Route::post('logout', [AuthController::class, 'logout'])->name('api.logout.user');
        Route::post('update', [UserController::class, 'updateProfile'])->name('api.user.profile.update');
        Route::post('change-password', [UserController::class, 'changePassword'])->name('api.user.change-password');

        // Folder API Routes
        Route::prefix('folders')->group(function () {
            Route::post('/store', [FolderController::class, 'folderCreate'])->name('api.folders.store');
            Route::get('/all', [FolderController::class, 'getAllFolders'])->name('api.folders.all');
            Route::get('/explorer', [FolderController::class, 'getExplorerData'])->name('api.folders.explorer');
            Route::delete('/{id}', [FolderController::class, 'removeFolder'])->name('api.folders.remove');
            Route::get('/{id}/files', [FolderController::class, 'folderFiles'])->name('api.folders.files');
            Route::put('/{id}', [FolderController::class, 'updateFolderName'])->name('api.folders.update');
        });

        // Document API Routes
        Route::prefix('documents')->group(function () {
            Route::post('/upload', [DocumentController::class, 'store'])->name('api.documents.upload');
            Route::put('/{id}', [DocumentController::class, 'update'])->name('api.documents.update');
            Route::delete('/{id}', [DocumentController::class, 'destroy'])->name('api.documents.destroy');
            Route::post('/{id}/share', [DocumentController::class, 'share'])->name('documents.share');
            Route::get('/{id}/permissions', [DocumentController::class, 'getPermissions'])->name('documents.permissions');
            Route::post('/{id}/revoke', [DocumentController::class, 'revokePermission'])->name('documents.revoke');
        });
        
        Route::get('share', [DocumentController::class, 'sharedWithMe'])->name('user.get.share.documents');
        Route::get('notifications',[AdminUserController::class, 'getNotification'])->name('user.get.notification');
    });
});