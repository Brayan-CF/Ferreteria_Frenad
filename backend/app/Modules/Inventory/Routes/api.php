<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Controllers\InventarioController;
use Modules\Inventory\Controllers\MovimientoInventarioController;

/*
|--------------------------------------------------------------------------
| Inventory Module API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    
    // Inventario
    Route::get('/inventario', [InventarioController::class, 'index']);
    Route::get('/inventario/statistics', [InventarioController::class, 'statistics']);
    Route::get('/inventario/stock-bajo', [InventarioController::class, 'stockBajo']);
    Route::get('/inventario/producto/{productoId}', [InventarioController::class, 'stockProducto']);
    Route::post('/inventario/transferir', [InventarioController::class, 'transferir']);
    Route::post('/inventario/ajustar', [InventarioController::class, 'ajustar']);
    Route::put('/inventario/stock-minimo', [InventarioController::class, 'actualizarStockMinimo']);

    // Movimientos de Inventario (Kardex)
    Route::get('/movimientos-inventario', [MovimientoInventarioController::class, 'index']);
    Route::get('/movimientos-inventario/statistics', [MovimientoInventarioController::class, 'statistics']);
    Route::get('/movimientos-inventario/kardex/{productoId}', [MovimientoInventarioController::class, 'kardexProducto']);
});
