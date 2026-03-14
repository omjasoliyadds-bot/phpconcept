<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;  
use App\Http\Controllers\Api\FolderController;  
use App\Http\Controllers\Api\DocumentController;


// Public API Routes
Route::prefix('v1')->group(function () {
    Route::post('register', [UserController::class, 'store'])->name('api.user.store');
    Route::post('login', [UserController::class, 'login'])->name('api.login.user');
    Route::get('activate-account/{token}', [UserController::class,'activateAccount'])
    ->name('activate.account');
    // Protected API Routes
    Route::middleware(['auth:sanctum', 'activated'])->group(function () {
        Route::get('user', function (Request $request) {
            return $request->user();
        })->name('api.user');

        Route::post('logout', [UserController::class, 'logout'])->name('api.logout.user');
        Route::post('update', [UserController::class, 'updateProfile'])->name('user.profile.update');
        Route::post('change-password', [UserController::class, 'changePassword'])->name('api.user.change-password');

        // Folder Routes
        Route::prefix('folders')->group(function () {

            Route::post('/store', [FolderController::class, 'folderCreate'])->name('folders.store');
            Route::get('/all', [FolderController::class, 'getAllFolders'])->name('folders.all');
            Route::get('/explorer', [FolderController::class, 'getExplorerData'])->name('folders.explorer');
            Route::delete('/{id}', [FolderController::class, 'removeFolder'])->name('folders.remove');
            Route::get('/{id}/files', [FolderController::class, 'folderFiles'])->name('folders.files');
            Route::put('/{id}', [FolderController::class, 'updateFolderName'])->name('folders.update');

        });

        // Document Routes
        Route::prefix('documents')->group(function () {
            Route::post('/upload', [DocumentController::class, 'store'])->name('api.documents.upload');
            Route::put('/{id}', [DocumentController::class, 'update'])->name('api.documents.update');
            Route::delete('/{id}', [DocumentController::class, 'destroy'])->name('api.documents.destroy');
            Route::get('/{id}/download', [DocumentController::class, 'download'])->name('api.documents.download');
        });
    });
});