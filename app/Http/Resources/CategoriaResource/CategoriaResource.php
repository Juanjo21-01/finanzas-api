<?php

namespace App\Http\Resources\CategoriaResource;

use App\Http\Resources\SubcategoriaResource\SubcategoriaResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoriaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'tipo' => $this->tipo,
            'es_sistema' => $this->user_id === null,
            'subcategorias' => SubcategoriaResource::collection(
                $this->whenLoaded('subcategorias')
            ),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
