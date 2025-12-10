<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Controllers\InventarioController;
use Modules\Inventory\Controllers\MovimientoInventarioController;
use Modules\Inventory\Controllers\AlmacenController;

/*
|--------------------------------------------------------------------------
| Inventory Module API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    
    // Almacenes
    Route::get('/almacenes', [AlmacenController::class, 'index']);
    Route::get('/almacenes/list', [AlmacenController::class, 'list']);
    Route::get('/almacenes/{id}', [AlmacenController::class, 'show']);
    Route::post('/almacenes', [AlmacenController::class, 'store']);
    Route::put('/almacenes/{id}', [AlmacenController::class, 'update']);
    Route::delete('/almacenes/{id}', [AlmacenController::class, 'destroy']);

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
