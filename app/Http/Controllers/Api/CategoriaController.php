<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoriaRequest\CategoriaIndexRequest;
use App\Http\Requests\CategoriaRequest\CategoriaStoreRequest;
use App\Http\Requests\CategoriaRequest\CategoriaUpdateRequest;
use App\Http\Resources\CategoriaResource\CategoriaCollection;
use App\Http\Resources\CategoriaResource\CategoriaResource;
use App\Models\Categoria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    /**
     * Display the categories visible to the authenticated user.
     */
    public function index(CategoriaIndexRequest $request): CategoriaCollection
    {
        $filtros = $request->validated();
        $userId = (int) $request->user()->getAuthIdentifier();

        $categorias = Categoria::query()
            ->visiblesPara($userId)
            ->with('subcategorias')
            ->when(
                isset($filtros['tipo']),
                fn ($query) => $query->where('tipo', $filtros['tipo'])
            )
            ->orderBy('tipo')
            ->orderBy('nombre')
            ->get();

        return new CategoriaCollection($categorias);
    }

    /**
     * Store a personal category for the authenticated user.
     */
    public function store(CategoriaStoreRequest $request): JsonResponse
    {
        $categoria = $request->user()->categorias()->create($request->validated());
        $categoria->load('subcategorias');

        return (new CategoriaResource($categoria))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display a category visible to the authenticated user.
     */
    public function show(Request $request, string $categoria): CategoriaResource
    {
        $registro = Categoria::query()
            ->visiblesPara((int) $request->user()->getAuthIdentifier())
            ->with('subcategorias')
            ->findOrFail($categoria);

        return new CategoriaResource($registro);
    }

    /**
     * Update a personal category owned by the authenticated user.
     */
    public function update(
        CategoriaUpdateRequest $request,
        string $categoria
    ): CategoriaResource|JsonResponse {
        $registro = $request->user()->categorias()->findOrFail($categoria);
        $datos = $request->validated();

        if (
            isset($datos['tipo'])
            && $datos['tipo'] !== $registro->tipo
            && $this->estaEnUso($registro, (int) $request->user()->getAuthIdentifier())
        ) {
            return response()->json([
                'message' => 'No se puede cambiar el tipo de una categoría que tiene movimientos asociados.',
            ], 409);
        }

        $registro->update($datos);
        $registro->load('subcategorias');

        return new CategoriaResource($registro);
    }

    /**
     * Delete a personal category owned by the authenticated user.
     */
    public function destroy(Request $request, string $categoria): JsonResponse
    {
        $registro = $request->user()->categorias()->findOrFail($categoria);
        $userId = (int) $request->user()->getAuthIdentifier();

        if ($this->estaEnUso($registro, $userId)) {
            return response()->json([
                'message' => 'No se puede eliminar una categoría que tiene movimientos asociados.',
            ], 409);
        }

        $registro->delete();

        return response()->json(null, 204);
    }

    private function estaEnUso(Categoria $categoria, int $userId): bool
    {
        return $categoria->ingresos()->where('user_id', $userId)->exists()
            || $categoria->egresos()->where('user_id', $userId)->exists();
    }
}
