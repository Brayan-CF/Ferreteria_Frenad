<?php

use Illuminate\Support\Facades\Route;
use Modules\Reports\Controllers\VentasReportController;
use Modules\Reports\Controllers\InventarioReportController;
use Modules\Reports\Controllers\ComprasReportController;
use Modules\Reports\Controllers\ClientesReportController;
use Modules\Reports\Controllers\FinancieroReportController;

/*
|--------------------------------------------------------------------------
| Rutas del Módulo de Reportes
|--------------------------------------------------------------------------
| Todas las rutas requieren autenticación y rol de Administrador o Gerente
*/

Route::middleware(['auth:sanctum', 'role:Administrador,Gerente'])->group(function () {
    
    // Reportes de Ventas
    Route::prefix('ventas')->group(function () {
        Route::get('resumen', [VentasReportController::class, 'resumen']);
        Route::get('por-vendedor', [VentasReportController::class, 'porVendedor']);
        Route::get('por-producto', [VentasReportController::class, 'porProducto']);
        Route::get('por-categoria', [VentasReportController::class, 'porCategoria']);
        Route::get('tendencia-diaria', [VentasReportController::class, 'tendenciaDiaria']);
        Route::get('descuentos', [VentasReportController::class, 'descuentos']);
    });

    // Reportes de Inventario
    Route::prefix('inventario')->group(function () {
        Route::get('stock-bajo', [InventarioReportController::class, 'stockBajo']);
        Route::get('movimientos', [InventarioReportController::class, 'movimientos']);
        Route::get('valorizado', [InventarioReportController::class, 'valorizado']);
        Route::get('sin-movimiento', [InventarioReportController::class, 'sinMovimiento']);
    });

    // Reportes de Compras
    Route::prefix('compras')->group(function () {
        Route::get('resumen', [ComprasReportController::class, 'resumen']);
        Route::get('por-proveedor', [ComprasReportController::class, 'porProveedor']);
        Route::get('por-producto', [ComprasReportController::class, 'porProducto']);
        Route::get('tendencia-diaria', [ComprasReportController::class, 'tendenciaDiaria']);
    });

    // Reportes de Clientes
    Route::prefix('clientes')->group(function () {
        Route::get('creditos-pendientes', [ClientesReportController::class, 'creditosPendientes']);
        Route::get('top-clientes', [ClientesReportController::class, 'topClientes']);
        Route::get('morosidad', [ClientesReportController::class, 'morosidad']);
        Route::get('clientes-nuevos', [ClientesReportController::class, 'clientesNuevos']);
    });

    // Reportes Financieros
    Route::prefix('financiero')->group(function () {
        Route::get('flujo-caja', [FinancieroReportController::class, 'flujoCaja']);
        Route::get('ingresos-egresos', [FinancieroReportController::class, 'ingresosEgresos']);
        Route::get('cierre-caja', [FinancieroReportController::class, 'cierreCaja']);
        Route::get('rentabilidad', [FinancieroReportController::class, 'rentabilidad']);
    });
});
