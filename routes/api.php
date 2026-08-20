<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\MeController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\RoleController;
use App\Http\Controllers\Api\Admin\PermissionController;
use App\Http\Controllers\Api\Admin\ModuleController;


// Employer
use App\Http\Controllers\Api\Employer\TeamController;
use App\Http\Controllers\Api\Employer\PayrollBatchController;

Route::prefix('auth')->group(function () {
    Route::post('/login', LoginController::class);
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me',      MeController::class);
        Route::post('/logout', LogoutController::class);
        Route::get('/sidebar', [ModuleController::class, 'sidebar'])->middleware('permission:sidebar.view');
    }); 
});

Route::prefix('admin')->middleware(['auth:sanctum', 'role:SUPER_ADMIN,ADMIN',])->group(function () {
    // User Management
    Route::get('/users',                [UserController::class, 'index'])->middleware('permission:users.view');
    Route::post('/users',               [UserController::class, 'store'])->middleware('permission:users.create');
    Route::get('/users/{uuid}',         [UserController::class, 'show'])->middleware('permission:users.view');
    Route::put('/users/{user:uuid}',    [UserController::class, 'update'])->middleware('permission:users.update');
    Route::delete('/users/{user:uuid}', [UserController::class, 'destroy'])->middleware('permission:users.delete');

    // Role Management
    Route::get('/roles',           [RoleController::class, 'index'])->middleware('permission:roles.view');
    Route::post('/roles',          [RoleController::class, 'store'])->middleware('permission:roles.create');
    Route::get('/roles/{uuid}',    [RoleController::class, 'show'])->middleware('permission:roles.view');
    Route::put('/roles/{uuid}',    [RoleController::class, 'update'])->middleware('permission:roles.update');
    Route::delete('/roles/{uuid}', [RoleController::class, 'destroy'])->middleware('permission:roles.delete');

    // Permission Management
    Route::get('/permissions',              [PermissionController::class, 'index'])->middleware('permission:roles.view');
    Route::put('/roles/{uuid}/permissions', [RoleController::class, 'updatePermissions'])->middleware('permission:roles.update');

   

});

Route::prefix('employer')->middleware(['auth:sanctum', 'role:EMPLOYER',])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Team Directory
    |--------------------------------------------------------------------------
    */
    Route::get('/team',           [TeamController::class, 'index'])->middleware('permission:team.view');
    Route::post('/team',          [TeamController::class, 'store'])->middleware('permission:team.create');
    Route::get('/team/{uuid}',    [TeamController::class, 'show'])->middleware('permission:team.view');
    Route::put('/team/{uuid}',    [TeamController::class, 'update'])->middleware('permission:team.update');
    Route::patch('/team/{uuid}',  [TeamController::class, 'update'])->middleware('permission:team.update');
    Route::delete('/team/{uuid}', [TeamController::class, 'destroy'])->middleware('permission:team.delete');
 

    /*
    |--------------------------------------------------------------------------
    | Payroll Batches
    |--------------------------------------------------------------------------
    */

    Route::get('/payroll-batches',                [PayrollBatchController::class, 'index'])->middleware('permission:payroll_batches.view');
    Route::post('/payroll-batches',               [PayrollBatchController::class, 'store'])->middleware('permission:payroll_batches.create');
    Route::get('/payroll-batches/{uuid}',         [PayrollBatchController::class, 'show'])->middleware('permission:payroll_batches.view');
    Route::patch('/payroll-batches/{uuid}',       [PayrollBatchController::class, 'update'])->middleware('permission:payroll_batches.update');
    Route::post('/payroll-batches/{uuid}/submit', [PayrollBatchController::class, 'submit'])->middleware('permission:payroll_batches.submit');
   

});