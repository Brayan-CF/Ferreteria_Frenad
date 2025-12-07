<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Controllers\AuthController;
use Modules\Auth\Controllers\UsuarioController;

/*
|--------------------------------------------------------------------------
| Auth Module API Routes
|--------------------------------------------------------------------------
*/

// Rutas públicas (sin autenticación)
Route::post('/auth/login', [AuthController::class, 'login']);

// Rutas protegidas (requieren autenticación)
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/profile', [AuthController::class, 'profile']);
    Route::post('/auth/change-password', [AuthController::class, 'changePassword']);

    // Usuarios (solo administradores)
    Route::middleware('role:Administrador')->group(function () {
        Route::get('/usuarios/statistics', [UsuarioController::class, 'statistics']);
        Route::post('/usuarios/{usuario}/activate', [UsuarioController::class, 'activate']);
        Route::apiResource('usuarios', UsuarioController::class);
    });
});