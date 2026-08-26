<?php

namespace App\Http\Requests\IngresoRequest;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IngresoStoreRequest extends FormRequest
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
                'required',
                'integer',
                Rule::exists('categorias', 'id')->where(function (QueryBuilder $query) use ($userId): void {
                    $query->where('tipo', 'ingreso')
                        ->where(function (QueryBuilder $query) use ($userId): void {
                            $query->whereNull('user_id')->orWhere('user_id', $userId);
                        });
                }),
            ],
            'fecha' => ['required', 'date_format:Y-m-d'],
            'fuente' => ['required', 'string', 'max:150'],
            'monto' => ['required', 'string', 'regex:/^\d{1,10}(?:\.\d{1,2})?$/'],
            'notas' => ['nullable', 'string'],
        ];
    }
}
