<?php

namespace App\Http\Resources\SubcategoriaResource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SubcategoriaCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var class-string<SubcategoriaResource>
     */
    public $collects = SubcategoriaResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
        ];
    }
}
