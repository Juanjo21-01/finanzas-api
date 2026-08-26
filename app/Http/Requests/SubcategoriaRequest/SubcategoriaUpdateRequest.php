<?php

namespace App\Http\Requests\SubcategoriaRequest;

use App\Models\Subcategoria;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;

class SubcategoriaUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['sometimes', 'string', 'max:80'],
        ];
    }

    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $subcategoria = Subcategoria::query()
                ->whereHas('categoria', function (Builder $query): void {
                    $query->where('user_id', $this->user()->getAuthIdentifier());
                })
                ->find($this->route('subcategoria'));

            if ($subcategoria === null) {
                return;
            }

            $duplicada = Subcategoria::query()
                ->whereKeyNot($subcategoria->id)
                ->where('categoria_id', $subcategoria->categoria_id)
                ->where('nombre', $this->input('nombre', $subcategoria->nombre))
                ->whereHas('categoria', function (Builder $query): void {
                    $query->where('user_id', $this->user()->getAuthIdentifier());
                })
                ->exists();

            if ($duplicada) {
                $validator->errors()->add(
                    'nombre',
                    'La categoría ya tiene una subcategoría con ese nombre.'
                );
            }
        });
    }
}
