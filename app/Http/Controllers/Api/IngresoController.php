<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IngresoRequest\IngresoIndexRequest;
use App\Http\Requests\IngresoRequest\IngresoStoreRequest;
use App\Http\Requests\IngresoRequest\IngresoUpdateRequest;
use App\Http\Resources\IngresoResource\IngresoCollection;
use App\Http\Resources\IngresoResource\IngresoResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IngresoController extends Controller
{
    /**
     * Display the authenticated user's incomes.
     */
    public function index(IngresoIndexRequest $request): IngresoCollection
    {
        $filtros = $request->validated();
        $anio = isset($filtros['anio']) ? (int) $filtros['anio'] : null;
        $mes = isset($filtros['mes']) ? (int) $filtros['mes'] : null;

        $consulta = $request->user()
            ->ingresos()
            ->with('categoria')
            ->when(
                $anio !== null && $mes !== null,
                fn ($query) => $query->delMes($anio, $mes)
            )
            ->when(
                $anio !== null && $mes === null,
                fn ($query) => $query
                    ->where('fecha', '>=', sprintf('%04d-01-01', $anio))
                    ->where('fecha', '<', sprintf('%04d-01-01', $anio + 1))
            )
            ->orderByDesc('fecha')
            ->orderByDesc('id');

        return new IngresoCollection($consulta->get());
    }

    /**
     * Store a new income for the authenticated user.
     */
    public function store(IngresoStoreRequest $request): JsonResponse
    {
        $ingreso = $request->user()->ingresos()->create($request->validated());
        $ingreso->load('categoria');

        return (new IngresoResource($ingreso))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display one income owned by the authenticated user.
     */
    public function show(Request $request, string $ingreso): IngresoResource
    {
        $registro = $request->user()
            ->ingresos()
            ->with('categoria')
            ->findOrFail($ingreso);

        return new IngresoResource($registro);
    }

    /**
     * Update one income owned by the authenticated user.
     */
    public function update(IngresoUpdateRequest $request, string $ingreso): IngresoResource
    {
        $registro = $request->user()->ingresos()->findOrFail($ingreso);
        $registro->update($request->validated());
        $registro->load('categoria');

        return new IngresoResource($registro);
    }

    /**
     * Delete one income owned by the authenticated user.
     */
    public function destroy(Request $request, string $ingreso): JsonResponse
    {
        $registro = $request->user()->ingresos()->findOrFail($ingreso);
        $registro->delete();

        return response()->json(null, 204);
    }
}
