<?php

namespace App\Http\Requests;

use App\Models\Display;
use App\Models\DisplayAdvertisement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDisplayAdvertisementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', DisplayAdvertisement::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'display_id' => ['required', 'integer', 'exists:displays,id'],
            'advertisement_id' => ['required', 'integer', 'exists:advertisements,id'],
            'position' => ['nullable', 'integer', 'min:1'],
            'transition' => ['nullable', 'string', 'max:50', 'in:fade,slide,zoom'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $user = $this->user();

            if (! $user || ! $user->tenant_id) {
                return;
            }

            $display = Display::find($this->input('display_id'));
            $advertisement = \App\Models\Advertisement::find($this->input('advertisement_id'));

            if ($display && $advertisement && (int) $display->tenant_id !== (int) $advertisement->tenant_id) {
                $validator->errors()->add('display_id', 'El display y la publicidad deben pertenecer al mismo tenant.');
            }

            if ($display && ! $user->hasRole('superadmin') && (int) $display->tenant_id !== (int) $user->tenant_id) {
                $validator->errors()->add('display_id', 'El display seleccionado no pertenece a tu tenant.');
            }

            if ($advertisement && ! $user->hasRole('superadmin') && (int) $advertisement->tenant_id !== (int) $user->tenant_id) {
                $validator->errors()->add('advertisement_id', 'La publicidad seleccionada no pertenece a tu tenant.');
            }

            $exists = \App\Models\DisplayAdvertisement::query()
                ->where('display_id', $this->input('display_id'))
                ->where('advertisement_id', $this->input('advertisement_id'))
                ->exists();

            if ($exists) {
                $validator->errors()->add('advertisement_id', 'Ya existe esta publicidad asociada a este display.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'display_id.required' => 'El display es obligatorio.',
            'display_id.exists' => 'El display seleccionado no existe.',
            'advertisement_id.required' => 'La publicidad es obligatoria.',
            'advertisement_id.exists' => 'La publicidad seleccionada no existe.',
            'position.integer' => 'La posición debe ser un número entero.',
            'position.min' => 'La posición mínima permitida es :min.',
            'transition.in' => 'La transición seleccionada no es válida.',
            'is_active.boolean' => 'El estado activo debe ser verdadero o falso.',
        ];
    }

    public function attributes(): array
    {
        return [
            'display_id' => 'display',
            'advertisement_id' => 'publicidad',
            'position' => 'posición',
            'transition' => 'transición',
            'is_active' => 'estado activo',
        ];
    }
}
