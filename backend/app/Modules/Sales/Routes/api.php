<?php

use Illuminate\Support\Facades\Route;
use Modules\Sales\Controllers\VentaController;

/*
|--------------------------------------------------------------------------
| Sales Module API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    
    // Ventas
    Route::get('/ventas/statistics', [VentaController::class, 'statistics']);
    Route::get('/ventas/hoy', [VentaController::class, 'ventasHoy']);
    Route::post('/ventas/{venta}/anular', [VentaController::class, 'anular']);
    Route::apiResource('ventas', VentaController::class)->except(['update', 'destroy']);
});