<?php

namespace App\Http\Resources\SubcategoriaResource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubcategoriaResource extends JsonResource
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
            'categoria_id' => $this->categoria_id,
            'nombre' => $this->nombre,
            'categoria' => $this->whenLoaded('categoria', fn () => [
                'id' => $this->categoria->id,
                'nombre' => $this->categoria->nombre,
                'tipo' => $this->categoria->tipo,
                'es_sistema' => $this->categoria->user_id === null,
            ]),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
