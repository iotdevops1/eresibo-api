<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Auth\LoginController;

Route::prefix('auth')->group(function () {

    Route::post('/login', LoginController::class);

});