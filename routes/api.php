<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('register', [UserController::class, 'register'])->middleware('is_superuser');
    
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/links/store', [LinkController::class, 'store']);
    Route::get('/links/index', [LinkController::class, 'index']);
    Route::get('/links/show/{token}', [LinkController::class, 'show']);
    Route::get('/links/redirect/{token}', [LinkController::class, 'redirect']);
});
