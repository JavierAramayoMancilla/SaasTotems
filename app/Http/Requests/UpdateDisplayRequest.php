<?php

namespace App\Http\Requests;

use App\Models\Display;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDisplayRequest extends FormRequest
{
    public function authorize(): bool
    {
        $display = $this->route('display');

        return $this->user()?->can('update', $display) ?? false;
    }

    public function rules(): array
    {
        $display = $this->route('display');

        return [
            'code' => [
                'sometimes',
                'string',
                'max:30',
                Rule::unique('displays', 'code')->ignore($display->id),
            ],

            'name' => [
                'sometimes',
                'string',
                'max:150',
            ],

            'status' => [
                'sometimes',
                'string',
                'in:active,inactive,suspended',
            ],

            'last_sync_at' => [
                'nullable',
                'date',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'code.string' => 'El código del display debe ser texto.',
            'code.max' => 'El código del display no puede superar los :max caracteres.',
            'code.unique' => 'Ya existe un display con este código.',

            'name.string' => 'El nombre del display debe ser texto.',
            'name.max' => 'El nombre del display no puede superar los :max caracteres.',

            'status.in' => 'El estado del display debe ser active, inactive o suspended.',

            'last_sync_at.date' => 'La última sincronización debe ser una fecha válida.',
        ];
    }

    public function attributes(): array
    {
        return [
            'code' => 'código',
            'name' => 'nombre',
            'status' => 'estado',
            'last_sync_at' => 'última sincronización',
        ];
    }
}