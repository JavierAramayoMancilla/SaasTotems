<?php

namespace App\Http\Requests;

use App\Models\Display;
use Illuminate\Foundation\Http\FormRequest;

class StoreDisplayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Display::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:30',
                'unique:displays,code',
            ],

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'status' => [
                'nullable',
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
            'code.required' => 'El código del display es obligatorio.',
            'code.string' => 'El código del display debe ser texto.',
            'code.max' => 'El código del display no puede superar los :max caracteres.',
            'code.unique' => 'Ya existe un display con este código.',

            'name.required' => 'El nombre del display es obligatorio.',
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