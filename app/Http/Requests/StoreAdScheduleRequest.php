<?php

namespace App\Http\Requests;

use App\Models\DisplayAdvertisement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreAdScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        // El superadmin puede trabajar con cualquier tenant.
        if ($user->hasRole('superadmin')) {
            return true;
        }

        $displayAdvertisement = DisplayAdvertisement::with('display')
            ->find($this->input('display_advertisement_id'));

        if (! $displayAdvertisement || ! $displayAdvertisement->display) {
            return false;
        }

        // El display debe pertenecer al tenant del usuario.
        return (int) $displayAdvertisement->display->tenant_id === (int) $user->tenant_id;
    }

    public function rules(): array
    {
        return [
            'display_advertisement_id' => [
                'required',
                'integer',
                'exists:display_advertisements,id',
            ],

            'day_of_week' => [
                'required',
                'integer',
                'between:0,6',
            ],

            'start_time' => [
                'required',
                'date_format:H:i',
            ],

            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
            ],

            'starts_at' => [
                'nullable',
                'date',
            ],

            'ends_at' => [
                'nullable',
                'date',
                'after_or_equal:starts_at',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {

            $user = $this->user();

            if (! $user || $user->hasRole('superadmin')) {
                return;
            }

            $displayAdvertisement = DisplayAdvertisement::with('display')
                ->find($this->input('display_advertisement_id'));

            if (
                ! $displayAdvertisement ||
                ! $displayAdvertisement->display ||
                (int) $displayAdvertisement->display->tenant_id !== (int) $user->tenant_id
            ) {
                $validator->errors()->add(
                    'display_advertisement_id',
                    'La publicidad seleccionada no pertenece a tu tenant.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'display_advertisement_id.required' =>
                'Debes seleccionar una publicidad asociada a un display.',

            'display_advertisement_id.integer' =>
                'La asociación display-publicidad debe ser un número entero.',

            'display_advertisement_id.exists' =>
                'La asociación display-publicidad seleccionada no existe.',

            'day_of_week.required' =>
                'El día de la semana es obligatorio.',

            'day_of_week.integer' =>
                'El día de la semana debe ser un número entero.',

            'day_of_week.between' =>
                'El día de la semana debe estar entre 0 y 6.',

            'start_time.required' =>
                'La hora de inicio es obligatoria.',

            'start_time.date_format' =>
                'La hora de inicio debe tener formato HH:MM.',

            'end_time.required' =>
                'La hora de finalización es obligatoria.',

            'end_time.date_format' =>
                'La hora de finalización debe tener formato HH:MM.',

            'end_time.after' =>
                'La hora de finalización debe ser posterior a la hora de inicio.',

            'starts_at.date' =>
                'La fecha de inicio debe ser válida.',

            'ends_at.date' =>
                'La fecha de finalización debe ser válida.',

            'ends_at.after_or_equal' =>
                'La fecha de finalización debe ser igual o posterior a la fecha de inicio.',
        ];
    }

    public function attributes(): array
    {
        return [
            'display_advertisement_id' => 'publicidad del display',
            'day_of_week' => 'día de la semana',
            'start_time' => 'hora de inicio',
            'end_time' => 'hora de finalización',
            'starts_at' => 'fecha de inicio',
            'ends_at' => 'fecha de finalización',
        ];
    }
}