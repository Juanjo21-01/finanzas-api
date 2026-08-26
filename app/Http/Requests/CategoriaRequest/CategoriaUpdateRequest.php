<?php

namespace App\Http\Requests\CategoriaRequest;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoriaUpdateRequest extends FormRequest
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
        return [
            'nombre' => ['sometimes', 'string', 'max:80'],
            'tipo' => ['sometimes', Rule::in(['ingreso', 'egreso'])],
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

            $nombre = $this->input('nombre', $categoria->nombre);
            $tipo = $this->input('tipo', $categoria->tipo);

            $duplicada = $this->user()
                ->categorias()
                ->whereKeyNot($categoria->id)
                ->where('nombre', $nombre)
                ->where('tipo', $tipo)
                ->exists();

            if ($duplicada) {
                $validator->errors()->add(
                    'nombre',
                    'Ya tienes una categoría con ese nombre y tipo.'
                );
            }
        });
    }
}
