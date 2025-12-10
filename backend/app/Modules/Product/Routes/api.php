<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Controllers\ProductoController;
use Modules\Product\Controllers\CategoriaController;
use Modules\Product\Controllers\MarcaController;
use Modules\Product\Controllers\UnidadMedidaController;

/*
|--------------------------------------------------------------------------
| Product Module API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    
    // Productos
    Route::get('/productos/statistics', [ProductoController::class, 'statistics']);
    Route::post('/productos/{producto}/activate', [ProductoController::class, 'activate']);
    Route::get('/productos/buscar-codigo-barras', [ProductoController::class, 'buscarPorCodigoBarras']);
    Route::get('/productos/buscar-sku', [ProductoController::class, 'buscarPorSKU']);
    Route::apiResource('productos', ProductoController::class);

    // Categorías
    Route::apiResource('categorias', CategoriaController::class);

    // Marcas
    Route::apiResource('marcas', MarcaController::class);

    // Unidades de Medida
    Route::apiResource('unidades', UnidadMedidaController::class);
});