<?php

use Illuminate\Support\Facades\Route;
use Modules\Customer\Controllers\ClienteController;
use Modules\Customer\Controllers\CreditoController;

/*
|--------------------------------------------------------------------------
| Customer Module API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    
    // Clientes
    Route::get('/clientes/statistics', [ClienteController::class, 'statistics']);
    Route::post('/clientes/{cliente}/activate', [ClienteController::class, 'activate']);
    Route::get('/clientes/{cliente}/estado-cuenta', [ClienteController::class, 'estadoCuenta']);
    Route::get('/clientes/{cliente}/historial-compras', [ClienteController::class, 'historialCompras']);
    Route::apiResource('clientes', ClienteController::class);

    // Créditos
    Route::get('/creditos/statistics', [CreditoController::class, 'statistics']);
    Route::get('/creditos/vencidos', [CreditoController::class, 'vencidos']);
    Route::get('/creditos/por-vencer', [CreditoController::class, 'porVencer']);
    Route::post('/creditos/{credito}/pagar', [CreditoController::class, 'registrarPago']);
    Route::apiResource('creditos', CreditoController::class)->only(['index', 'show']);
});