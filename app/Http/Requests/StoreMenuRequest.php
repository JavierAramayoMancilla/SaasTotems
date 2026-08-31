<?php

namespace App\Http\Requests;

use App\Models\Advertisement;
use App\Models\Menu;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Menu::class) ?? false;
    }

    public function rules(): array
    {
        $user = $this->user();

        return [
            'advertisement_id' => [
                'required',
                'integer',
                'exists:advertisements,id',
                function ($attribute, $value, $fail) use ($user) {
                    $advertisement = Advertisement::find($value);

                    if (! $advertisement) {
                        return;
                    }

                    if (
                        ! $user->hasRole('superadmin') &&
                        (int) $advertisement->tenant_id !== (int) $user->tenant_id
                    ) {
                        $fail('La publicidad seleccionada no pertenece a tu tenant.');
                    }
                },
            ],

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'slug' => [
                'required',
                'string',
                'max:150',
                Rule::unique('menus', 'slug')
                    ->where(fn ($query) =>
                        $query->where('tenant_id', $user->tenant_id)
                    ),
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'version' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'published_at' => [
                'nullable',
                'date',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'advertisement_id.required' => 'La publicidad es obligatoria.',
            'advertisement_id.integer' => 'La publicidad debe ser un número entero.',
            'advertisement_id.exists' => 'La publicidad seleccionada no existe.',

            'name.required' => 'El nombre del menú es obligatorio.',
            'name.string' => 'El nombre del menú debe ser texto.',
            'name.max' => 'El nombre del menú no puede superar los :max caracteres.',

            'slug.required' => 'El slug del menú es obligatorio.',
            'slug.string' => 'El slug del menú debe ser texto.',
            'slug.max' => 'El slug del menú no puede superar los :max caracteres.',
            'slug.unique' => 'Ya existe un menú con este slug en este tenant.',

            'is_active.boolean' => 'El estado activo debe ser verdadero o falso.',

            'version.integer' => 'La versión debe ser un número entero.',
            'version.min' => 'La versión mínima permitida es :min.',

            'published_at.date' => 'La fecha de publicación debe ser válida.',
        ];
    }

    public function attributes(): array
    {
        return [
            'advertisement_id' => 'publicidad',
            'name' => 'nombre',
            'slug' => 'slug',
            'is_active' => 'estado activo',
            'version' => 'versión',
            'published_at' => 'fecha de publicación',
        ];
    }
}