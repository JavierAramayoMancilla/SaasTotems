<?php

namespace App\Http\Requests;

use App\Models\DisplayAdvertisement;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAdScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $adSchedule = $this->route('ad_schedule');

        if (! $user || ! $adSchedule) {
            return false;
        }

        // El superadmin puede modificar cualquier horario.
        if ($user->hasRole('superadmin')) {
            return true;
        }

        $displayAdvertisement = $adSchedule->displayAdvertisement()
            ->with('display')
            ->first();

        if (! $displayAdvertisement || ! $displayAdvertisement->display) {
            return false;
        }

        // El horario debe pertenecer al tenant del usuario.
        return (int) $displayAdvertisement->display->tenant_id === (int) $user->tenant_id;
    }

    public function rules(): array
    {
        return [
            'display_advertisement_id' => [
                'sometimes',
                'integer',
                'exists:display_advertisements,id',
            ],

            'day_of_week' => [
                'sometimes',
                'integer',
                'between:0,6',
            ],

            'start_time' => [
                'sometimes',
                'date_format:H:i',
            ],

            'end_time' => [
                'sometimes',
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

            /*
             * Si se está cambiando el display-publicidad,
             * debemos comprobar que la nueva asociación
             * también pertenece al tenant del usuario.
             */
            if ($this->filled('display_advertisement_id')) {

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
            }
        });
    }

    public function messages(): array
    {
        return [
            'display_advertisement_id.integer' =>
                'La asociación display-publicidad debe ser un número entero.',

            'display_advertisement_id.exists' =>
                'La asociación display-publicidad seleccionada no existe.',

            'day_of_week.integer' =>
                'El día de la semana debe ser un número entero.',

            'day_of_week.between' =>
                'El día de la semana debe estar entre 0 y 6.',

            'start_time.date_format' =>
                'La hora de inicio debe tener formato HH:MM.',

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