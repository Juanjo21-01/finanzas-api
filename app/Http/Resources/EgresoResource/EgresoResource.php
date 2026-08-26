<?php

namespace App\Http\Resources\EgresoResource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EgresoResource extends JsonResource
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
            'subcategoria_id' => $this->subcategoria_id,
            'fecha' => $this->fecha?->toDateString(),
            'descripcion' => $this->descripcion,
            'monto' => $this->monto,
            'notas' => $this->notas,
            'categoria' => $this->whenLoaded('categoria', fn () => [
                'id' => $this->categoria->id,
                'nombre' => $this->categoria->nombre,
                'tipo' => $this->categoria->tipo,
            ]),
            'subcategoria' => $this->whenLoaded(
                'subcategoria',
                fn () => $this->subcategoria === null ? null : [
                    'id' => $this->subcategoria->id,
                    'nombre' => $this->subcategoria->nombre,
                ]
            ),
            'created_at' => $this->created_at?->toISOString()
        ];
    }
}
