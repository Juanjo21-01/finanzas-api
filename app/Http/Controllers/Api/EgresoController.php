<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EgresoRequest\EgresoIndexRequest;
use App\Http\Requests\EgresoRequest\EgresoStoreRequest;
use App\Http\Requests\EgresoRequest\EgresoUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EgresoController extends Controller
{
    /**
     * Display the authenticated user's expenses.
     */
    public function index(EgresoIndexRequest $request): JsonResponse
    {
        $filtros = $request->validated();
        $anio = isset($filtros['anio']) ? (int) $filtros['anio'] : null;
        $mes = isset($filtros['mes']) ? (int) $filtros['mes'] : null;

        $consulta = $request->user()
            ->egresos()
            ->with(['categoria', 'subcategoria'])
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

        return response()->json($consulta->get());
    }

    /**
     * Store a new expense for the authenticated user.
     */
    public function store(EgresoStoreRequest $request): JsonResponse
    {
        $egreso = $request->user()->egresos()->create($request->validated());
        $egreso->load(['categoria', 'subcategoria']);

        return response()->json($egreso, 201);
    }

    /**
     * Display one expense owned by the authenticated user.
     */
    public function show(Request $request, string $egreso): JsonResponse
    {
        $registro = $request->user()
            ->egresos()
            ->with(['categoria', 'subcategoria'])
            ->findOrFail($egreso);

        return response()->json($registro);
    }

    /**
     * Update one expense owned by the authenticated user.
     */
    public function update(EgresoUpdateRequest $request, string $egreso): JsonResponse
    {
        $registro = $request->user()->egresos()->findOrFail($egreso);
        $registro->update($request->validated());
        $registro->load(['categoria', 'subcategoria']);

        return response()->json($registro);
    }

    /**
     * Delete one expense owned by the authenticated user.
     */
    public function destroy(Request $request, string $egreso): JsonResponse
    {
        $registro = $request->user()->egresos()->findOrFail($egreso);
        $registro->delete();

        return response()->json(null, 204);
    }
}
