<?php

namespace App\Http\Requests;

use App\Models\Subcategoria;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EgresoStoreRequest extends FormRequest
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
                    $query->where('tipo', 'egreso')
                        ->where(function (QueryBuilder $query) use ($userId): void {
                            $query->whereNull('user_id')->orWhere('user_id', $userId);
                        });
                }),
            ],
            'subcategoria_id' => ['nullable', 'integer'],
            'fecha' => ['required', 'date_format:Y-m-d'],
            'descripcion' => ['required', 'string', 'max:150'],
            // String + regex prevents monetary input from becoming a float.
            'monto' => ['required', 'string', 'regex:/^\d{1,10}(?:\.\d{1,2})?$/'],
            'notas' => ['nullable', 'string'],
        ];
    }

    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $subcategoriaId = $this->input('subcategoria_id');

            if ($subcategoriaId === null || ! is_numeric($this->input('categoria_id'))) {
                return;
            }

            $esValida = Subcategoria::query()
                ->whereKey($subcategoriaId)
                ->where('categoria_id', $this->input('categoria_id'))
                ->whereHas('categoria', function (Builder $query): void {
                    $query->visiblesPara((int) $this->user()->getAuthIdentifier())
                        ->where('tipo', 'egreso');
                })
                ->exists();

            if (! $esValida) {
                $validator->errors()->add(
                    'subcategoria_id',
                    'La subcategoría no pertenece a la categoría seleccionada.'
                );
            }
        });
    }
}
