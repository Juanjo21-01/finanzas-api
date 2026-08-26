<?php

namespace App\Http\Requests\SubcategoriaRequest;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class SubcategoriaStoreRequest extends FormRequest
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
            'nombre' => ['required', 'string', 'max:80'],
        ];
    }

    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $categoria = $this->user()
                ->categorias()
                ->find($this->route('categoria'));

            if ($categoria === null) {
                return;
            }

            $duplicada = $categoria->subcategorias()
                ->where('nombre', $this->input('nombre'))
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
