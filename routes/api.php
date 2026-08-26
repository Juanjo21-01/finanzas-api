<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EgresoController;
use App\Http\Controllers\Api\IngresoController;
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

    // RUTAS PARA DASHBOARD
});
