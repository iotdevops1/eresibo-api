<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\MeController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Admin\UserController;


Route::prefix('auth')->group(function () {
    Route::post('/login', LoginController::class);
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me',      MeController::class);
        Route::post('/logout', LogoutController::class);
    }); 
});

Route::prefix('admin')->middleware(['auth:sanctum', 'role:SUPER_ADMIN,ADMIN',])->group(function () {
    Route::get('/users',           [UserController::class, 'index'])->middleware('permission:users.view');
    Route::post('/users',          [UserController::class, 'store'])->middleware('permission:users.create');
    Route::get('/users/{uuid}', [UserController::class, 'show'])->middleware('permission:users.view');
    Route::put('/users/{user:uuid}',    [UserController::class, 'update'])->middleware('permission:users.update');
    Route::delete('/users/{user:uuid}', [UserController::class, 'destroy'])->middleware('permission:users.delete');
});