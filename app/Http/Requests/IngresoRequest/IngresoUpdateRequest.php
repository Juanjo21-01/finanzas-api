<?php

namespace App\Http\Requests\IngresoRequest;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IngresoUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $userId = $this->user()->getAuthIdentifier();

        return [
            'categoria_id' => [
                'sometimes',
                'integer',
                Rule::exists('categorias', 'id')->where(function (QueryBuilder $query) use ($userId): void {
                    $query->where('tipo', 'ingreso')
                        ->where(function (QueryBuilder $query) use ($userId): void {
                            $query->whereNull('user_id')->orWhere('user_id', $userId);
                        });
                }),
            ],
            'fecha' => ['sometimes', 'date_format:Y-m-d'],
            'fuente' => ['sometimes', 'string', 'max:150'],
            'monto' => ['sometimes', 'string', 'regex:/^\d{1,10}(?:\.\d{1,2})?$/'],
            'notas' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
