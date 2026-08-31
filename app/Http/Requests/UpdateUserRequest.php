<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->route('user');

        return $this->user()?->can('update', $user) ?? false;
    }

    public function rules(): array
    {
        $currentUser = $this->user();
        $user = $this->route('user');

        $rules = [
            'code' => [
                'sometimes',
                'nullable',
                'string',
                'max:30',
                Rule::unique('users', 'code')->ignore($user->id),
            ],

            'name' => [
                'sometimes',
                'string',
                'max:150',
            ],

            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'password' => [
                'sometimes',
                'confirmed',
                Rules\Password::defaults(),
            ],

            'status' => [
                'sometimes',
                'string',
                'in:active,inactive,suspended',
            ],

            'role' => [
                'sometimes',
                'string',
                'in:user,tenant_admin',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | Solo el SuperAdmin puede cambiar el tenant.
        |--------------------------------------------------------------------------
        */

        if ($currentUser->isSuperAdmin()) {
            $rules['tenant_id'] = [
                'sometimes',
                'required',
                'integer',
                'exists:tenants,id',
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'tenant_id.required' => 'El tenant es obligatorio.',
            'tenant_id.integer' => 'El tenant debe ser un número entero.',
            'tenant_id.exists' => 'El tenant seleccionado no existe.',

            'code.string' => 'El código debe ser texto.',
            'code.max' => 'El código no puede superar los :max caracteres.',
            'code.unique' => 'Ya existe un usuario con este código.',

            'name.string' => 'El nombre del usuario debe ser texto.',
            'name.max' => 'El nombre del usuario no puede superar los :max caracteres.',

            'email.email' => 'El email debe ser una dirección válida.',
            'email.max' => 'El email no puede superar los :max caracteres.',
            'email.unique' => 'Ya existe un usuario con este email.',

            'password.confirmed' => 'La confirmación de la contraseña no coincide.',

            'status.in' => 'El estado debe ser active, inactive o suspended.',

            'role.in' => 'El rol seleccionado no es válido.',
        ];
    }

    public function attributes(): array
    {
        return [
            'tenant_id' => 'tenant',
            'code' => 'código',
            'name' => 'nombre',
            'email' => 'email',
            'password' => 'contraseña',
            'status' => 'estado',
            'role' => 'rol',
        ];
    }
}