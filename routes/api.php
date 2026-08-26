<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EgresoController;
use App\Http\Controllers\Api\IngresoController;
use App\Http\Controllers\Api\SubcategoriaController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->group(function (): void {
    // RUTAS PARA MÓDULO DE EGRESOS
    Route::apiResource('egresos', EgresoController::class);

    // RUTAS PARA MÓDULO DE INGRESOS
    Route::apiResource('ingresos', IngresoController::class);

    // RUTAS PARA MÓDULO DE CATEGORIAS
    Route::apiResource('categorias', CategoriaController::class);

    // RUTAS PARA MÓDULO DE SUBCATEGORIAS
    Route::apiResource('categorias.subcategorias', SubcategoriaController::class)
        ->shallow();

    // RUTAS PARA DASHBOARD
    Route::get('dashboard/resumen', [DashboardController::class, 'resumen']);
});
