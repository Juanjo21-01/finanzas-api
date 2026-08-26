<?php

namespace App\Http\Resources\IngresoResource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IngresoResource extends JsonResource
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
            'fecha' => $this->fecha?->toDateString(),
            // 'fuente' => $this->fuente,
            'monto' => $this->monto,
            'notas' => $this->notas,
            'categoria' => $this->whenLoaded('categoria', fn () => [
                'id' => $this->categoria->id,
                'nombre' => $this->categoria->nombre,
                'tipo' => $this->categoria->tipo,
            ]),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
