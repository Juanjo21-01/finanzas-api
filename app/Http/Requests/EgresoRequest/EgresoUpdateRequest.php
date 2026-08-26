<?php

namespace App\Http\Requests\EgresoRequest;

use App\Models\Subcategoria;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EgresoUpdateRequest extends FormRequest
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
                    $query->where('tipo', 'egreso')
                        ->where(function (QueryBuilder $query) use ($userId): void {
                            $query->whereNull('user_id')->orWhere('user_id', $userId);
                        });
                }),
            ],
            'subcategoria_id' => ['sometimes', 'nullable', 'integer'],
            'fecha' => ['sometimes', 'date_format:Y-m-d'],
            'descripcion' => ['sometimes', 'string', 'max:150'],
            // String + regex prevents monetary input from becoming a float.
            'monto' => ['sometimes', 'string', 'regex:/^\d{1,10}(?:\.\d{1,2})?$/'],
            'notas' => ['sometimes', 'nullable', 'string'],
        ];
    }

    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $registro = $this->user()
                ->egresos()
                ->find($this->route('egreso'));

            if ($registro === null) {
                return;
            }

            $categoriaId = $this->input('categoria_id', $registro->categoria_id);
            $subcategoriaId = $this->input('subcategoria_id', $registro->subcategoria_id);

            if ($subcategoriaId === null || ! is_numeric($categoriaId)) {
                return;
            }

            $esValida = Subcategoria::query()
                ->whereKey($subcategoriaId)
                ->where('categoria_id', $categoriaId)
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
