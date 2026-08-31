<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Listar usuarios.
     *
     * SUPERADMIN:
     * - Ve todos los usuarios.
     * - Puede filtrar por tenant.
     * - Puede filtrar por rol y estado.
     *
     * TENANT_ADMIN:
     * - Solo ve usuarios de su tenant.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

        $currentUser = $request->user();

        $query = User::query()
            ->with([
                'tenant',
                'roles',
            ])
            ->orderByDesc('created_at');

        /*
        |--------------------------------------------------------------------------
        | SUPERADMIN
        |--------------------------------------------------------------------------
        */

        if ($currentUser->hasRole('superadmin')) {

            if ($request->filled('tenant_id')) {
                $query->where('tenant_id', $request->tenant_id);
            }

        } else {

            /*
            |--------------------------------------------------------------------------
            | TENANT_ADMIN
            |--------------------------------------------------------------------------
            */

            $query->where('tenant_id', $currentUser->tenant_id);
        }

        /*
        |--------------------------------------------------------------------------
        | Buscar por nombre, email o código
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Filtro por estado
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        /*
        |--------------------------------------------------------------------------
        | Filtro por rol
        |--------------------------------------------------------------------------
        */

        if ($request->filled('role')) {

            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        $users = $query
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Users/Index', [
            'users' => $users,

            'filters' => $request->only([
                'search',
                'tenant_id',
                'status',
                'role',
            ]),

            'isSuperAdmin' => $currentUser->hasRole('superadmin'),
        ]);
    }

    /**
     * Mostrar formulario de creación.
     */
    public function create(): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('Users/Create');
    }

    /**
     * Crear usuario.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $currentUser = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Determinar tenant
        |--------------------------------------------------------------------------
        |
        | SUPERADMIN:
        | Puede seleccionar el tenant al que pertenecerá el usuario.
        |
        | TENANT_ADMIN:
        | El usuario se crea automáticamente dentro de su propio tenant.
        |
        */

        if ($currentUser->hasRole('superadmin')) {
            $tenantId = $validated['tenant_id'] ?? null;
        } else {
            $tenantId = $currentUser->tenant_id;
        }

        $role = $validated['role'];

        /*
        |--------------------------------------------------------------------------
        | Los usuarios normales y tenant_admin siempre pertenecen
        | a un tenant.
        |--------------------------------------------------------------------------
        */

        if (blank($tenantId)) {
            throw ValidationException::withMessages([
                'tenant_id' => 'El usuario debe pertenecer a un tenant.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Solo puede existir UN tenant_admin por tenant.
        |--------------------------------------------------------------------------
        */

        if ($role === 'tenant_admin') {

            // Un tenant_admin NO puede crear otro administrador.
            if (! $currentUser->hasRole('superadmin')) {
                throw ValidationException::withMessages([
                    'role' => 'Solo el SuperAdmin puede asignar el rol de administrador.',
                ]);
            }

            $adminExists = User::where('tenant_id', $tenantId)
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'tenant_admin');
                })
                ->exists();

            if ($adminExists) {
                throw ValidationException::withMessages([
                    'role' => 'Este tenant ya tiene un administrador.',
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Crear usuario
        |--------------------------------------------------------------------------
        */

        $user = User::create([
            'tenant_id' => $tenantId,
            'code' => $validated['code'] ?? null,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => $validated['status'] ?? 'active',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Asignar rol
        |--------------------------------------------------------------------------
        */

        $user->syncRoles($role);

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    /**
     * Mostrar usuario.
     */
    public function show(User $user): Response
    {
        $this->authorize('view', $user);

        $user->load([
            'tenant',
            'roles',
        ]);

        return Inertia::render('Users/Show', [
            'user' => $user,
        ]);
    }

    /**
     * Mostrar formulario de edición.
     */
    public function edit(User $user): Response
    {
        $this->authorize('update', $user);

        $user->load([
            'tenant',
            'roles',
        ]);

        return Inertia::render('Users/Edit', [
            'user' => $user,
        ]);
    }

    /**
     * Actualizar usuario.
     */
    public function update(
        UpdateUserRequest $request,
        User $user
    ): RedirectResponse {

        $this->authorize('update', $user);

        $validated = $request->validated();

        $currentUser = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Un TENANT_ADMIN no puede cambiar el tenant.
        |--------------------------------------------------------------------------
        */

        if (
            ! $currentUser->hasRole('superadmin')
            && isset($validated['tenant_id'])
            && (int) $validated['tenant_id'] !== (int) $currentUser->tenant_id
        ) {
            throw ValidationException::withMessages([
                'tenant_id' => 'No puedes asignar un usuario a otro tenant.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Determinar nuevo tenant
        |--------------------------------------------------------------------------
        */

        $tenantId = $validated['tenant_id']
            ?? $user->tenant_id;

        /*
        |--------------------------------------------------------------------------
        | Validar cambio a tenant_admin
        |--------------------------------------------------------------------------
        */

        if (isset($validated['role']) && $validated['role'] === 'tenant_admin') {

            if (blank($tenantId)) {
                throw ValidationException::withMessages([
                    'role' => 'Un administrador debe pertenecer a un tenant.',
                ]);
            }

            $adminExists = User::where('tenant_id', $tenantId)
                ->where('id', '!=', $user->id)
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'tenant_admin');
                })
                ->exists();

            if ($adminExists) {
                throw ValidationException::withMessages([
                    'role' => 'Este tenant ya tiene un administrador.',
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | No permitir modificar el tenant desde aquí
        | si el Request no lo permite.
        |--------------------------------------------------------------------------
        */

        if (! $currentUser->hasRole('superadmin')) {
            unset($validated['tenant_id']);
        }

        /*
        |--------------------------------------------------------------------------
        | Password
        |--------------------------------------------------------------------------
        */

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        /*
        |--------------------------------------------------------------------------
        | Rol
        |--------------------------------------------------------------------------
        */

        $role = $validated['role'] ?? null;

        unset($validated['role']);

        /*
        |--------------------------------------------------------------------------
        | Actualizar datos
        |--------------------------------------------------------------------------
        */

        $user->fill($validated);
        $user->save();

        /*
        |--------------------------------------------------------------------------
        | Actualizar rol
        |--------------------------------------------------------------------------
        */

        if ($role !== null) {
            $user->syncRoles($role);
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Desactivar usuario.
     *
     * No se elimina físicamente.
     */
    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        /*
        |--------------------------------------------------------------------------
        | El SuperAdmin no debe ser desactivado.
        |--------------------------------------------------------------------------
        */

        if ($user->hasRole('superadmin')) {
            throw ValidationException::withMessages([
                'user' => 'El SuperAdmin no puede ser desactivado.',
            ]);
        }

        $user->update([
            'status' => 'inactive',
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuario deshabilitado correctamente.');
    }
}

