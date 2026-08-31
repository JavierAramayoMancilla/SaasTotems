<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Mostrar formulario de registro.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Registrar un nuevo cliente del SaaS.
     *
     * Flujo:
     *
     * Cliente se registra
     *      ↓
     * Se crea su Tenant
     *      ↓
     * Se crea su usuario
     *      ↓
     * Se asigna tenant_admin
     *      ↓
     * Login automático
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'tenant_name' => [
                'required',
                'string',
                'max:150',
            ],

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:' . User::class,
            ],

            'password' => [
                'required',
                'confirmed',
                Rules\Password::defaults(),
            ],
        ]);

        $user = DB::transaction(function () use ($request) {

            /*
            |--------------------------------------------------------------------------
            | Crear Tenant
            |--------------------------------------------------------------------------
            */

            $tenantName = trim($request->tenant_name);

            $tenant = Tenant::create([
                'code' => 'TEN-' . strtoupper(Str::random(8)),
                'name' => $tenantName,
                'slug' => Str::slug($tenantName) . '-' . Str::lower(Str::random(5)),
                'description' => null,
                'status' => 'active',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Crear usuario propietario del Tenant
            |--------------------------------------------------------------------------
            */

            $user = User::create([
                'tenant_id' => $tenant->id,
                'code' => 'USR-' . strtoupper(Str::random(8)),
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'status' => 'active',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Asignar administrador del Tenant
            |--------------------------------------------------------------------------
            |
            | El primer usuario que registra el servicio se convierte
            | automáticamente en tenant_admin.
            |
            */

            $user->assignRole('tenant_admin');

            return $user;
        });

        /*
        |--------------------------------------------------------------------------
        | Evento de registro
        |--------------------------------------------------------------------------
        */

        event(new Registered($user));

        /*
        |--------------------------------------------------------------------------
        | Login automático
        |--------------------------------------------------------------------------
        */

        Auth::login($user);

        /*
        |--------------------------------------------------------------------------
        | Redirigir al Dashboard
        |--------------------------------------------------------------------------
        */

        return redirect()->route('dashboard');
    }
}

