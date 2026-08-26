<?php

namespace App\Http\Requests\CategoriaRequest;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoriaStoreRequest extends FormRequest
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
            'nombre' => [
                'required',
                'string',
                'max:80',
                Rule::unique('categorias', 'nombre')->where(
                    fn (QueryBuilder $query) => $query
                        ->where('user_id', $userId)
                        ->where('tipo', $this->input('tipo'))
                ),
            ],
            'tipo' => ['required', Rule::in(['ingreso', 'egreso'])],
        ];
    }
}
