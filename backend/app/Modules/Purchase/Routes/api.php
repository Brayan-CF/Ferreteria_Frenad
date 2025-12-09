<?php

use Illuminate\Support\Facades\Route;
use Modules\Purchase\Controllers\ProveedorController;
use Modules\Purchase\Controllers\CompraController;

/*
|--------------------------------------------------------------------------
| Purchase Module API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    
    // Proveedores
    Route::get('/proveedores/statistics', [ProveedorController::class, 'statistics']);
    Route::post('/proveedores/{proveedor}/activate', [ProveedorController::class, 'activate']);
    Route::apiResource('proveedores', ProveedorController::class);

    // Compras
    Route::get('/compras/statistics', [CompraController::class, 'statistics']);
    Route::apiResource('compras', CompraController::class)->except(['update', 'destroy']);
});