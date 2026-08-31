<?php

namespace App\Http\Requests;

use App\Models\Advertisement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdvertisementRequest extends FormRequest
{
    public function authorize(): bool
    {
        $advertisement = $this->route('advertisement');

        return $this->user()?->can('update', $advertisement) ?? false;
    }

    public function rules(): array
    {
        $advertisement = $this->route('advertisement');

        return [
            'code' => ['sometimes', 'nullable', 'string', 'max:30', Rule::unique('advertisements', 'code')
                    ->ignore($advertisement->id)
                    ->where(fn ($query) => $query->where('tenant_id', $this->user()->tenant_id))],
            'name' => ['sometimes', 'string', 'max:150'],
            'type' => ['required', 'string', 'in:image,video,html'],
            'media_path' => ['nullable', 'string', 'max:500'],
            'duration' => ['sometimes', 'integer', 'min:5', 'max:15'],
            'is_active' => ['sometimes', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => 'Ya existe una publicidad con este código.',
            'code.max' => 'El código no puede superar los :max caracteres.',
            'name.string' => 'El nombre de la publicidad debe ser texto.',
            'name.max' => 'El nombre de la publicidad no puede superar los :max caracteres.',
            'type.string' => 'El tipo de publicidad debe ser texto.',
            'type.max' => 'El tipo de publicidad no puede superar los :max caracteres.',
            'media_path.string' => 'La ruta del archivo debe ser texto.',
            'media_path.max' => 'La ruta del archivo no puede superar los :max caracteres.',
            'duration.integer' => 'La duración debe ser un número entero.',
            'duration.min' => 'La duración mínima permitida es de :min segundos.',
            'duration.max' => 'La duración máxima permitida es de :max segundos.',
            'is_active.boolean' => 'El estado activo debe ser verdadero o falso.',
            'starts_at.date' => 'La fecha de inicio debe ser una fecha válida.',
            'ends_at.date' => 'La fecha de fin debe ser una fecha válida.',
            'ends_at.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
        ];
    }

    public function attributes(): array
    {
        return [
            'code' => 'código',
            'name' => 'nombre',
            'type' => 'tipo',
            'media_path' => 'ruta de archivo',
            'duration' => 'duración',
            'is_active' => 'estado activo',
            'starts_at' => 'fecha de inicio',
            'ends_at' => 'fecha de fin',
        ];
    }
}
