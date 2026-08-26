<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubcategoriaRequest\SubcategoriaStoreRequest;
use App\Http\Requests\SubcategoriaRequest\SubcategoriaUpdateRequest;
use App\Http\Resources\SubcategoriaResource\SubcategoriaCollection;
use App\Http\Resources\SubcategoriaResource\SubcategoriaResource;
use App\Models\Categoria;
use App\Models\Subcategoria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubcategoriaController extends Controller
{
    /**
     * Display the subcategories of a visible category.
     */
    public function index(Request $request, string $categoria): SubcategoriaCollection
    {
        $categoriaVisible = Categoria::query()
            ->visiblesPara((int) $request->user()->getAuthIdentifier())
            ->findOrFail($categoria);

        return new SubcategoriaCollection(
            $categoriaVisible->subcategorias()->orderBy('nombre')->get()
        );
    }

    /**
     * Store a subcategory in a personal category.
     */
    public function store(
        SubcategoriaStoreRequest $request,
        string $categoria
    ): JsonResponse {
        $categoriaPropia = $request->user()->categorias()->findOrFail($categoria);
        $subcategoria = $categoriaPropia->subcategorias()->create($request->validated());
        $subcategoria->load('categoria');

        return (new SubcategoriaResource($subcategoria))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display a subcategory visible to the authenticated user.
     */
    public function show(Request $request, string $subcategoria): SubcategoriaResource
    {
        $registro = Subcategoria::query()
            ->whereHas('categoria', function (Builder $query) use ($request): void {
                $query->visiblesPara((int) $request->user()->getAuthIdentifier());
            })
            ->with('categoria')
            ->findOrFail($subcategoria);

        return new SubcategoriaResource($registro);
    }

    /**
     * Update a subcategory that belongs to a personal category.
     */
    public function update(
        SubcategoriaUpdateRequest $request,
        string $subcategoria
    ): SubcategoriaResource {
        $registro = $this->subcategoriaPropia($request, $subcategoria);
        $registro->update($request->validated());
        $registro->load('categoria');

        return new SubcategoriaResource($registro);
    }

    /**
     * Delete a subcategory that belongs to a personal category.
     */
    public function destroy(Request $request, string $subcategoria): JsonResponse
    {
        $registro = $this->subcategoriaPropia($request, $subcategoria);
        $registro->delete();

        return response()->json(null, 204);
    }

    private function subcategoriaPropia(Request $request, string $subcategoria): Subcategoria
    {
        return Subcategoria::query()
            ->whereHas('categoria', function (Builder $query) use ($request): void {
                $query->where('user_id', $request->user()->getAuthIdentifier());
            })
            ->findOrFail($subcategoria);
    }
}
