<?php

namespace App\Http\Requests\DashboardRequest;

use Illuminate\Foundation\Http\FormRequest;

class DashboardResumenAnualRequest extends FormRequest
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
            'anio' => ['required', 'integer', 'digits:4', 'between:1900,2100'],
        ];
    }
}
