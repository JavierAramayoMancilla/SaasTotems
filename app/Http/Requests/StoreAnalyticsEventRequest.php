<?php

namespace App\Http\Requests;

use App\Models\Advertisement;
use App\Models\Display;
use App\Models\DisplayAdvertisement;
use App\Models\MenuItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnalyticsEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        if ($user->hasRole('superadmin')) {
            return true;
        }

        $display = Display::find($this->input('display_id'));

        return $display
            && (int) $display->tenant_id === (int) $user->tenant_id;
    }

    public function rules(): array
    {
        return [
            'display_id' => [
                'required',
                'integer',
                'exists:displays,id',
            ],

            'event_type' => [
                'required',
                'string',
                'max:50',
            ],

            'advertisement_id' => [
                'nullable',
                'integer',
                'exists:advertisements,id',
            ],

            'menu_item_id' => [
                'nullable',
                'integer',
                'exists:menu_items,id',
            ],

            'session_id' => [
                'nullable',
                'string',
                'max:100',
            ],

            'started_at' => [
                'required',
                'date',
            ],

            'duration' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'metadata' => [
                'nullable',
                'array',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {

            $user = $this->user();

            if (! $user) {
                return;
            }

            $display = Display::find($this->input('display_id'));

            if (! $display) {
                return;
            }

            /*
             * Validar tenant del display.
             */
            if (
                ! $user->hasRole('superadmin')
                && (int) $display->tenant_id !== (int) $user->tenant_id
            ) {
                $validator->errors()->add(
                    'display_id',
                    'El display seleccionado no pertenece a tu tenant.'
                );

                return;
            }

            /*
             * Validar publicidad.
             */
            if ($this->filled('advertisement_id')) {

                $advertisement = Advertisement::find(
                    $this->input('advertisement_id')
                );

                if (! $advertisement) {
                    return;
                }

                if (
                    ! $user->hasRole('superadmin')
                    && (int) $advertisement->tenant_id !== (int) $user->tenant_id
                ) {
                    $validator->errors()->add(
                        'advertisement_id',
                        'La publicidad seleccionada no pertenece a tu tenant.'
                    );

                    return;
                }

                /*
                 * Comprobar que la publicidad realmente
                 * esté asociada al display.
                 */
                $associationExists = DisplayAdvertisement::query()
                    ->where('display_id', $display->id)
                    ->where('advertisement_id', $advertisement->id)
                    ->exists();

                if (! $associationExists) {
                    $validator->errors()->add(
                        'advertisement_id',
                        'La publicidad no está asociada al display seleccionado.'
                    );
                }
            }

            /*
             * Validar elemento del menú.
             */
            if ($this->filled('menu_item_id')) {

                $menuItem = MenuItem::with('menu')
                    ->find($this->input('menu_item_id'));

                if (! $menuItem || ! $menuItem->menu) {
                    $validator->errors()->add(
                        'menu_item_id',
                        'El elemento del menú seleccionado no es válido.'
                    );

                    return;
                }

                if (
                    ! $user->hasRole('superadmin')
                    && (int) $menuItem->menu->tenant_id !== (int) $user->tenant_id
                ) {
                    $validator->errors()->add(
                        'menu_item_id',
                        'El elemento del menú no pertenece a tu tenant.'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'display_id.required' =>
                'El display es obligatorio.',

            'display_id.integer' =>
                'El display debe ser un número entero.',

            'display_id.exists' =>
                'El display seleccionado no existe.',

            'event_type.required' =>
                'El tipo de evento es obligatorio.',

            'event_type.string' =>
                'El tipo de evento debe ser texto.',

            'event_type.max' =>
                'El tipo de evento no puede superar los :max caracteres.',

            'advertisement_id.integer' =>
                'La publicidad debe ser un número entero.',

            'advertisement_id.exists' =>
                'La publicidad seleccionada no existe.',

            'menu_item_id.integer' =>
                'El elemento del menú debe ser un número entero.',

            'menu_item_id.exists' =>
                'El elemento del menú seleccionado no existe.',

            'session_id.string' =>
                'La sesión debe ser texto.',

            'session_id.max' =>
                'La sesión no puede superar los :max caracteres.',

            'started_at.required' =>
                'La fecha de inicio del evento es obligatoria.',

            'started_at.date' =>
                'La fecha de inicio del evento debe ser válida.',

            'duration.integer' =>
                'La duración debe ser un número entero.',

            'duration.min' =>
                'La duración no puede ser negativa.',

            'metadata.array' =>
                'Los metadatos deben ser un arreglo válido.',
        ];
    }

    public function attributes(): array
    {
        return [
            'display_id' => 'display',
            'event_type' => 'tipo de evento',
            'advertisement_id' => 'publicidad',
            'menu_item_id' => 'elemento del menú',
            'session_id' => 'sesión',
            'started_at' => 'fecha de inicio',
            'duration' => 'duración',
            'metadata' => 'metadatos',
        ];
    }
}