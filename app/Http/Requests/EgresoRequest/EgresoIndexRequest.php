<?php

namespace App\Http\Requests\EgresoRequest;

use Illuminate\Foundation\Http\FormRequest;

class EgresoIndexRequest extends FormRequest
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
            'anio' => ['nullable', 'required_with:mes', 'integer', 'digits:4', 'between:1900,2100'],
            'mes' => ['nullable', 'integer', 'between:1,12'],
        ];
    }
}
