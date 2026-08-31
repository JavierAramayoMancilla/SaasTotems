<?php

namespace App\Http\Requests;

use App\Models\Display;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDisplayAdvertisementRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $displayAdvertisement = $this->route('display_advertisement');

        if (! $user || ! $displayAdvertisement) {
            return false;
        }

        if ($user->hasRole('superadmin')) {
            return true;
        }

        if (! $user->tenant_id) {
            return false;
        }

        $display = $displayAdvertisement->display;

        if (! $display) {
            return false;
        }

        return (int) $display->tenant_id === (int) $user->tenant_id;
    }

    public function rules(): array
    {
        return [
            'display_id' => ['sometimes', 'integer', 'exists:displays,id'],
            'advertisement_id' => ['sometimes', 'integer', 'exists:advertisements,id'],
            'position' => ['sometimes', 'integer', 'min:1'],
            'transition' => ['sometimes', 'string', 'max:50', 'in:fade,slide,zoom'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $user = $this->user();
            $displayAdvertisement = $this->route('display_advertisement');

            if (! $user || ! $displayAdvertisement) {
                return;
            }

            $displayId = $this->input('display_id', $displayAdvertisement->display_id);
            $advertisementId = $this->input('advertisement_id', $displayAdvertisement->advertisement_id);

            $display = Display::find($displayId);
            $advertisement = \App\Models\Advertisement::find($advertisementId);

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
                ->where('display_id', $displayId)
                ->where('advertisement_id', $advertisementId)
                ->whereKeyNot($displayAdvertisement->id)
                ->exists();

            if ($exists) {
                $validator->errors()->add('advertisement_id', 'Ya existe esta publicidad asociada a este display.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'display_id.integer' => 'El display debe ser un número entero.',
            'display_id.exists' => 'El display seleccionado no existe.',
            'advertisement_id.integer' => 'La publicidad debe ser un número entero.',
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
