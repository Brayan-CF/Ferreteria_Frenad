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
    Route::get('/ventas/daily', [VentaController::class, 'ventasDiarias']);
    Route::get('/ventas/top-products', [VentaController::class, 'topProductos']);
    Route::get('/ventas/by-payment-method', [VentaController::class, 'porMetodoPago']);
    Route::post('/ventas/{venta}/anular', [VentaController::class, 'anular']);
    Route::apiResource('ventas', VentaController::class)->except(['update', 'destroy']);
});