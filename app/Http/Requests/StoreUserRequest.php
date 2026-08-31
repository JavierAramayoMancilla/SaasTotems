<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', User::class) ?? false;
    }

    public function rules(): array
    {
        $currentUser = $this->user();

        $rules = [
            'code' => [
                'nullable',
                'string',
                'max:30',
                'unique:users,code',
            ],

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'confirmed',
                Rules\Password::defaults(),
            ],

            'status' => [
                'nullable',
                'string',
                'in:active,inactive,suspended',
            ],

            'role' => [
                'required',
                'string',
                'in:user,tenant_admin',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | El SuperAdmin puede seleccionar el tenant.
        |--------------------------------------------------------------------------
        */

        if ($currentUser->isSuperAdmin()) {
            $rules['tenant_id'] = [
                'required',
                'integer',
                'exists:tenants,id',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Un Tenant Admin NO puede elegir tenant_id.
        | El controlador utilizará automáticamente su propio tenant.
        |--------------------------------------------------------------------------
        */

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

            'name.required' => 'El nombre del usuario es obligatorio.',
            'name.string' => 'El nombre del usuario debe ser texto.',
            'name.max' => 'El nombre del usuario no puede superar los :max caracteres.',

            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe ser una dirección válida.',
            'email.max' => 'El email no puede superar los :max caracteres.',
            'email.unique' => 'Ya existe un usuario con este email.',

            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',

            'status.in' => 'El estado debe ser active, inactive o suspended.',

            'role.required' => 'El rol es obligatorio.',
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