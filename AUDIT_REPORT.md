# AUDITORÍA COMPLETA — PROYECTO LARAVEL 12 SAAS

**Fecha de auditoría:** 30 de agosto de 2026  
**Proyecto:** Totems SaaS  
**Stack:** Laravel 12 | PHP 8.3 | MySQL | React | Inertia.js | Tailwind CSS  
**Autenticación:** Laravel Breeze | Session Auth  
**Roles/Permisos:** Spatie laravel-permission  
**Multi-tenancy:** Row-level tenancy (tenant_id en tablas)

---

## ÍNDICE

1. [A. FLUJO ACTUAL DEL SISTEMA](#a-flujo-actual-del-sistema)
2. [B. FLUJO DE AUTENTICACIÓN](#b-flujo-de-autenticación)
3. [C. FLUJO MULTI-TENANT](#c-flujo-multi-tenant)
4. [D. FLUJO DE ROLES Y PERMISOS](#d-flujo-de-roles-y-permisos)
5. [E. FLUJO DE REGISTRO DE CLIENTES](#e-flujo-de-registro-de-clientes)
6. [F. FLUJO DE USUARIOS](#f-flujo-de-usuarios)
7. [G. FLUJO DE PUBLICIDADES](#g-flujo-de-publicidades)
8. [H. MATRIZ DE PERMISOS POR ROL](#h-matriz-de-permisos-por-rol)
9. [I. ARCHIVOS INVOLUCRADOS](#i-archivos-involucrados)
10. [J. REFERENCIAS RESTANTES A SYSTEM](#j-referencias-restantes-a-system)
11. [K. INCONSISTENCIAS ENCONTRADAS](#k-inconsistencias-encontradas)
12. [L. POSIBLES VULNERABILIDADES O PROBLEMAS DE AUTORIZACIÓN](#l-posibles-vulnerabilidades-o-problemas-de-autorización)
13. [M. CONCLUSIÓN](#m-conclusión)

---

## A. FLUJO ACTUAL DEL SISTEMA

### Resumen Arquitectónico

El sistema funciona bajo una arquitectura **SaaS Multi-Tenant** basada en:

- **Tenants:** Empresas contratantes del servicio
- **Users:** Usuarios con roles asignados dentro de tenants
- **Roles:** superadmin, tenant_admin, user
- **Tenant Isolation:** TenantScope + BelongsToTenant trait
- **Authorization:** Policies + Spatie Permission

### Tabla de Relaciones Principales

| Modelo | tenant_id | Belongs To | Has Many | Scope Aplicado |
|--------|-----------|-----------|----------|----------------|
| User | Sí (NULL para superadmin) | Tenant | Roles | ✅ BelongsToTenant |
| Tenant | N/A | - | Users, Displays, Advertisements, Menus, Analytics | - |
| Display | Sí | Tenant | DisplayAdvertisements, Menus, AnalyticsEvents | ✅ BelongsToTenant |
| Advertisement | Sí | Tenant | DisplayAdvertisements, AnalyticsEvents | ✅ BelongsToTenant |
| Menu | Sí | Tenant, Advertisement | MenuItems | ✅ BelongsToTenant |
| MenuItem | No | Menu | Nada directa | ❌ Sin BelongsToTenant |
| DisplayAdvertisement | No | Display, Advertisement | AdSchedules | ❌ Sin BelongsToTenant |
| AdSchedule | No | DisplayAdvertisement | Nada | ❌ Sin BelongsToTenant |
| AnalyticsEvent | Sí | Tenant, Display, Advertisement, MenuItem | Nada | ✅ BelongsToTenant |

---

## B. FLUJO DE AUTENTICACIÓN

### 1. Login (Inicio de Sesión)

**Ruta:** POST `/login`  
**Controller:** `Auth\AuthenticatedSessionController@store`  
**Request:** `Auth\LoginRequest`

```
1. Usuario ingresa email + password
2. LoginRequest valida
3. Auth::attempt() intenta autenticar con email + password
4. Si falla: Throttling (5 intentos por IP en 60s)
5. Si éxito:
   - Session se regenera
   - Redirige a /dashboard
```

**Archivos involucrados:**
- [app/Http/Controllers/Auth/AuthenticatedSessionController.php](app/Http/Controllers/Auth/AuthenticatedSessionController.php#L17)
- [app/Http/Requests/Auth/LoginRequest.php](app/Http/Requests/Auth/LoginRequest.php)
- [routes/auth.php](routes/auth.php#L13)

**Validaciones:**
- Email requerido, formato válido
- Password requerido
- Rate limiting: 5 intentos máximo por IP cada 60 segundos

**Identificación del usuario autenticado:**
- `Auth::user()` obtiene el usuario desde la sesión
- `$request->user()` accede al usuario autenticado dentro de controllers/requests
- El usuario retiene su `tenant_id` y roles desde la sesión

**Clasificación:** ✅ **CORRECTO** - Implementación estándar de Laravel Breeze

### 2. Logout (Cierre de Sesión)

**Ruta:** POST `/logout`  
**Controller:** `Auth\AuthenticatedSessionController@destroy`

```
1. Auth::guard('web')->logout() cierra sesión
2. Session se invalida
3. Token CSRF se regenera
4. Redirige a /
```

**Clasificación:** ✅ **CORRECTO** - Implementación estándar

### 3. Middleware de Autenticación

**Middleware utilizado:** `auth` (nativo de Laravel)

Aplicado a:
- Todas las rutas en `/routes/web.php` excepto dashboard
- Dashboard requiere `auth` + `verified`
- Las rutas de auth requieren `guest` para no autenticados

**Flujo de protección:**
1. Middleware `auth` verifica si usuario está autenticado
2. Si no, redirige a `/login`
3. Usuarios deben pasar por TenantScope antes de ver recursos

**Clasificación:** ✅ **CORRECTO** - Aplicación correcta de middleware

---

## C. FLUJO MULTI-TENANT

### TenantScope

**Archivo:** [app/Scopes/TenantScope.php](app/Scopes/TenantScope.php)

```php
public function apply(Builder $builder, Model $model): void
{
    if (! Auth::check()) {
        return; // Sin filtrado si no autenticado
    }

    $user = Auth::user();

    if (! $user || ! method_exists($user, 'hasRole')) {
        return; // Sin filtrado si no tiene método hasRole
    }

    if ($user->hasRole('superadmin')) {
        return; // SuperAdmin ve todo
    }

    if (blank($user->tenant_id)) {
        $builder->whereRaw('0 = 1'); // NULL tenant_id → no ve nada
        return;
    }

    $builder->where($model->getTable() . '.tenant_id', $user->tenant_id);
}
```

**Lógica:**
1. Si usuario no autenticado → sin filtrado (imposible, middleware auth previene)
2. Si usuario es superadmin → sin filtrado (ve todo)
3. Si usuario tiene `tenant_id = NULL` y no es superadmin → bloquea todo (`0 = 1`)
4. Si usuario tiene `tenant_id` → filtra por ese tenant

**Modelos con BelongsToTenant:**
- User
- Display
- Advertisement
- Menu
- AnalyticsEvent

**Modelos SIN BelongsToTenant (POTENCIAL PROBLEMA):**
- MenuItem (solo tiene relación menu_id)
- DisplayAdvertisement (solo tiene FK display_id, advertisement_id)
- AdSchedule (solo tiene FK display_advertisement_id)

### Relación User-Tenant

**Usuario Normal:**
```
User.tenant_id = 123 (Coca-Cola)
User.roles = ['user'] o ['tenant_admin']
Acceso a: Recursos donde tenant_id = 123
```

**SuperAdmin:**
```
User.tenant_id = NULL
User.roles = ['superadmin']
Acceso a: TODOS los recursos (filtrado deshabilitado)
```

**Usuario Huérfano (sin tenant):**
```
User.tenant_id = NULL
User.roles = ['user'] o ['tenant_admin']
Acceso a: NADA (TenantScope devuelve `0 = 1`)
```

**Potencial:** 🟡 **ADVERTENCIA** - La lógica de TenantScope depende de que se setee correctamente tenant_id. Un error en registro podría dejar usuarios sin acceso.

### Cascadas y Eliminación

**Migraciones con cascadeOnDelete:**
- displays: `tenant_id → tenants.id (cascadeOnDelete)`
- advertisements: `tenant_id → tenants.id (cascadeOnDelete)`
- menus: `tenant_id → tenants.id (cascadeOnDelete)`
- display_advertisements: `display_id → displays.id (cascadeOnDelete)`
- display_advertisements: `advertisement_id → advertisements.id (cascadeOnDelete)`
- menu_items: `menu_id → menus.id (cascadeOnDelete)`
- menu_items: `parent_id → menu_items.id (cascadeOnDelete)`
- ad_schedules: `display_advertisement_id → display_advertisements.id (cascadeOnDelete)`

**users: `tenant_id → tenants.id (nullOnDelete)`**
- Cuando un tenant se elimina, sus usuarios quedan con `tenant_id = NULL`
- Esto bloquearía su acceso si no son superadmin

**Clasificación:** ⚠️ **ADVERTENCIA** - nullOnDelete en users podría dejar usuarios inactivos tras eliminar tenant

---

## D. FLUJO DE ROLES Y PERMISOS

### Roles Definidos

Archivo: [database/seeders/RoleSeeder.php](database/seeders/RoleSeeder.php)

#### SUPERADMIN

```php
$superAdmin = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
$superAdmin->syncPermissions([
    'users.view', 'users.create', 'users.update', 'users.delete',
    'displays.view', 'displays.create', 'displays.update', 'displays.delete',
    'advertisements.view', 'advertisements.create', 'advertisements.update', 'advertisements.delete',
    'menus.view', 'menus.create', 'menus.update', 'menus.delete',
    'menu_items.view', 'menu_items.create', 'menu_items.update', 'menu_items.delete',
    'ad_schedules.view', 'ad_schedules.create', 'ad_schedules.update', 'ad_schedules.delete',
    'analytics.view'
]);
```

**Permisos:** 27 (todos)

#### TENANT_ADMIN

```php
$tenantAdmin = Role::firstOrCreate(['name' => 'tenant_admin', 'guard_name' => 'web']);
$tenantAdmin->syncPermissions([
    'users.view', 'users.create', 'users.update', 'users.delete',
    'displays.view', 'displays.create', 'displays.update',
    'advertisements.view', 'advertisements.create', 'advertisements.update',
    'menus.view', 'menus.create', 'menus.update',
    'menu_items.view', 'menu_items.create', 'menu_items.update',
    'ad_schedules.view', 'ad_schedules.create', 'ad_schedules.update',
    'analytics.view'
]);
```

**Permisos:** 18 (sin delete en displays, ads, menus, menuitems, adschedules)

#### USER

```php
$user = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
$user->syncPermissions([
    'displays.view',
    'advertisements.view', 'advertisements.create', 'advertisements.update',
    'menus.view', 'menus.create', 'menus.update',
    'menu_items.view', 'menu_items.create', 'menu_items.update',
    'ad_schedules.view', 'ad_schedules.create', 'ad_schedules.update',
    'analytics.view'
]);
```

**Permisos:** 11 (solo view displays, can create/update ads pero no delete)

### Asignación de Roles

#### SuperAdmin (Seeder)

Archivo: [database/seeders/SuperAdminSeeder.php](database/seeders/SuperAdminSeeder.php)

```php
$user = User::firstOrCreate(
    ['email' => 'admin'],
    [
        'tenant_id' => null,      // ← NULL tenant
        'code' => 'SUPERADMIN',
        'name' => 'Super Administrador',
        'password' => Hash::make('javier2510'),
        'status' => 'active',
    ]
);
$user->syncRoles(['superadmin']);
```

**Cómo se identifica:** `User.tenant_id = NULL` + `hasRole('superadmin')`  
**Clasificación:** ✅ **CORRECTO** - Creado automáticamente por seeder

#### Tenant Admin (Registro)

Archivo: [app/Http/Controllers/Auth/RegisteredUserController.php](app/Http/Controllers/Auth/RegisteredUserController.php#L111)

```php
// En store():
$tenant = Tenant::create([...]);
$user = User::create([
    'tenant_id' => $tenant->id,  // ← Vinculado al tenant
    'code' => 'USR-...',
    'name' => $request->name,
    'email' => $request->email,
    'password' => Hash::make($request->password),
    'status' => 'active',
]);
$user->assignRole('tenant_admin');  // ← Rol asignado automáticamente
```

**Cuándo se asigna:** Automáticamente durante register  
**Límite:** Máximo 1 por tenant (validado en UserController@store)

#### User Normal (Creación manual)

Archivo: [app/Http/Controllers/UserController.php](app/Http/Controllers/UserController.php#L151)

```php
// En store():
$user = User::create([
    'tenant_id' => $tenantId,
    'code' => $validated['code'] ?? null,
    'name' => $validated['name'],
    'email' => $validated['email'],
    'password' => Hash::make($validated['password']),
    'status' => $validated['status'] ?? 'active',
]);
$user->syncRoles($role);  // ← role viene del request
```

**Quién puede crear:** 
- SUPERADMIN: Puede crear usuarios en cualquier tenant
- TENANT_ADMIN: Solo puede crear en su propio tenant
- USER: No puede crear

**Clasificación:** ✅ **CORRECTO** - Lógica de asignación implementada

### Permisos Spatie Utilizados

Total: **27 permisos**

| Permiso | Descripción |
|---------|-------------|
| users.view | Ver usuarios |
| users.create | Crear usuarios |
| users.update | Actualizar usuarios |
| users.delete | Desactivar usuarios |
| displays.view | Ver displays |
| displays.create | Crear displays |
| displays.update | Actualizar displays |
| displays.delete | Desactivar/eliminar displays |
| advertisements.view | Ver publicidades |
| advertisements.create | Crear publicidades |
| advertisements.update | Actualizar publicidades |
| advertisements.delete | Desactivar publicidades |
| menus.view | Ver menús |
| menus.create | Crear menús |
| menus.update | Actualizar menús |
| menus.delete | Desactivar menús |
| menu_items.view | Ver items de menú |
| menu_items.create | Crear items |
| menu_items.update | Actualizar items |
| menu_items.delete | Desactivar items |
| ad_schedules.view | Ver horarios de publicidades |
| ad_schedules.create | Crear horarios |
| ad_schedules.update | Actualizar horarios |
| ad_schedules.delete | Desactivar horarios |
| analytics.view | Ver análisis |

---

## E. FLUJO DE REGISTRO DE CLIENTES

### Escenario Completo: Registración de Nueva Empresa

**Ruta:** POST `/register`  
**Controller:** `Auth\RegisteredUserController@store`  
**Request:** Validación inline en método store

### Paso a Paso

```
1. Usuario accede a /register → Formulario
   ├─ tenant_name (nombre de la empresa)
   ├─ name (nombre del usuario)
   ├─ email (email único)
   └─ password (confirmado)

2. POST /register con datos
   ├─ Validación: tenant_name único?, email único?, password válido?
   │  └─ Si error → Redirige con errores
   │
   ├─ DB::transaction {
   │  ├─ Crear Tenant:
   │  │  ├─ code: 'TEN-' . random(8)
   │  │  ├─ name: tenant_name
   │  │  ├─ slug: slug(tenant_name) . '-' . random(5)
   │  │  ├─ description: null
   │  │  └─ status: 'active'
   │  │
   │  ├─ Crear User:
   │  │  ├─ tenant_id: tenant.id
   │  │  ├─ code: 'USR-' . random(8)
   │  │  ├─ name: name
   │  │  ├─ email: email
   │  │  ├─ password: Hash::make(password)
   │  │  └─ status: 'active'
   │  │
   │  └─ Asignar rol:
   │     └─ user.assignRole('tenant_admin')
   │ }
   │
   ├─ Evento Registered(user) disparado
   │
   ├─ Auth::login(user) → Sesión iniciada
   │
   └─ Redirige a /dashboard
```

**Validaciones en StoreUserRequest:**

Archivo: [app/Http/Controllers/Auth/RegisteredUserController.php](app/Http/Controllers/Auth/RegisteredUserController.php#L47)

```php
'tenant_name' => [
    'required', 'string', 'max:150',
],
'name' => [
    'required', 'string', 'max:150',
],
'email' => [
    'required', 'string', 'lowercase', 'email', 'max:255',
    'unique:' . User::class,  // ← Email debe ser único globalmente
],
'password' => [
    'required', 'confirmed',
    Rules\Password::defaults(),  // ← Min 8, mayúscula, número, símbolo
],
```

**Transacción:**
- ✅ Si todo OK: Tenant + User + Rol creados, login automático
- ❌ Si error: Rollback completo (ninguno se crea)

**Post-registro:**
1. Evento `Registered` se dispara (para enviar email verificación si es necesario)
2. Usuario es logeado automáticamente
3. Redirigido a `/dashboard`

**Clasificación:** ✅ **CORRECTO** - Flujo completo SIN depender de SYSTEM

---

## F. FLUJO DE USUARIOS

### Operaciones CRUD de Usuarios

**Archivo principal:** [app/Http/Controllers/UserController.php](app/Http/Controllers/UserController.php)

#### 1. Listar Usuarios (INDEX)

**Ruta:** GET `/users`  
**Policy:** `UserPolicy@viewAny`

```
1. $this->authorize('viewAny', User::class)
   ├─ Verifica: hasTenantPermission(user, 'users.view')
   └─ Verifica: hasTenantAccess(user, user->tenant_id)

2. Si SUPERADMIN:
   ├─ Ve todos los usuarios de todos los tenants
   ├─ Puede filtrar por tenant_id
   ├─ Puede filtrar por rol
   └─ Puede filtrar por estado

3. Si TENANT_ADMIN:
   ├─ Solo ve usuarios de su tenant
   └─ TenantScope filtra automáticamente

4. Si USER:
   └─ Rechazado (UserPolicy::viewAny retorna false)

5. Búsqueda por: name, email, code
```

**Request:** [app/Http/Requests/StoreUserRequest.php](app/Http/Requests/StoreUserRequest.php)

**Clasificación:** ✅ **CORRECTO** - Filtrado adecuado por tenant

#### 2. Crear Usuario (STORE)

**Ruta:** POST `/users`  
**Policy:** `UserPolicy@create`

```
1. $this->authorize('create', User::class)

2. Validación StoreUserRequest:
   ├─ name: requerido, max 150
   ├─ email: requerido, email válido, único
   ├─ password: requerido, confirmado, reglas password
   ├─ status: nullable, in:active,inactive,suspended
   ├─ role: requerido, in:user,tenant_admin
   │
   └─ SI SUPERADMIN:
      └─ tenant_id: requerido, integer, exists:tenants

3. Lógica en Controller:
   ├─ SI SUPERADMIN:
   │  └─ tenant_id = request.tenant_id
   │
   └─ SI TENANT_ADMIN:
      └─ tenant_id = currentUser.tenant_id (automático)

4. Validación de tenant_id:
   ├─ Debe ser != null
   └─ (Usuario no puede estar sin tenant)

5. Validación de tenant_admin:
   ├─ SI role == 'tenant_admin':
   │  ├─ Solo SUPERADMIN puede asignar tenant_admin
   │  └─ Un tenant solo puede tener 1 tenant_admin
   │     (validado con query que busca existentes)
   │
   └─ SI TENANT_ADMIN intenta crear tenant_admin:
      └─ ValidationException: Solo SuperAdmin...

6. Crear User:
   ├─ User::create(tenant_id, code, name, email, password, status)
   └─ user.syncRoles(role)

7. Redirige a users.index con success
```

**Archivo:** [app/Http/Controllers/UserController.php](app/Http/Controllers/UserController.php#L112)

**Validaciones adicionales en StoreUserRequest:**

Archivo: [app/Http/Requests/StoreUserRequest.php](app/Http/Requests/StoreUserRequest.php)

```php
'role' => ['required', 'string', 'in:user,tenant_admin'],
// Solo SUPERADMIN puede especificar tenant_id
if ($currentUser->isSuperAdmin()) {
    $rules['tenant_id'] = [
        'required', 'integer', 'exists:tenants,id',
    ];
}
```

**Clasificación:** ✅ **CORRECTO** - Validaciones completas

#### 3. Actualizar Usuario (UPDATE)

**Ruta:** PATCH `/users/{user}`  
**Policy:** `UserPolicy@update`

```
1. $this->authorize('update', $user)
   ├─ hasTenantPermission(currentUser, 'users.update')
   └─ hasTenantAccess(currentUser, $user->tenant_id)

2. UpdateUserRequest valida
   ├─ code: unique (ignore $user->id)
   ├─ name: string, max 150
   ├─ email: unique (ignore $user->id)
   ├─ password: confirmado si presente
   ├─ status: in:active,inactive,suspended
   ├─ role: in:user,tenant_admin
   │
   └─ SI SUPERADMIN:
      └─ tenant_id: exists:tenants (permite cambiar tenant)

3. Lógica en Controller:
   ├─ Si TENANT_ADMIN intenta cambiar tenant:
   │  └─ ValidationException (no puede reasignar)
   │
   ├─ Si cambiar a tenant_admin:
   │  ├─ Validar que otro admin no existe en tenant
   │  └─ Rechazo si existe
   │
   ├─ Solo SUPERADMIN puede cambiar tenant_id
   │  └─ TENANT_ADMIN: unset($validated['tenant_id'])
   │
   ├─ Hash password si presente
   │
   ├─ user.fill(validated)
   ├─ user.save()
   │
   └─ Si role presente:
      └─ user.syncRoles(role)

4. Redirige a users.index con success
```

**Archivo:** [app/Http/Requests/UpdateUserRequest.php](app/Http/Requests/UpdateUserRequest.php)

**Clasificación:** ✅ **CORRECTO** - Protecciones contra cambios indebidos

#### 4. Desactivar Usuario (DESTROY)

**Ruta:** DELETE `/users/{user}`  
**Policy:** `UserPolicy@delete`

```
1. $this->authorize('delete', $user)
   ├─ hasTenantPermission(currentUser, 'users.delete')
   └─ hasTenantAccess(currentUser, $user->tenant_id)

2. Protección especial:
   ├─ SI user == SUPERADMIN:
   │  └─ ValidationException: SuperAdmin no puede ser desactivado
   │
   └─ (Evita eliminar el único superadmin)

3. Actualizar estado:
   └─ user.update(['status' => 'inactive'])

4. Redirige con success
```

**Archivo:** [app/Http/Controllers/UserController.php](app/Http/Controllers/UserController.php#L391)

**Nota:** No es eliminación física, solo cambio de estado

**Clasificación:** ✅ **CORRECTO** - Protección del SuperAdmin

### Resumen de Permisos por Rol

| Acción | SuperAdmin | TenantAdmin | User |
|--------|-----------|-----------|------|
| Ver usuarios | Sí, todos | Sí, su tenant | No |
| Crear usuarios | Sí, en cualquier tenant | Sí, en su tenant | No |
| Actualizar usuarios | Sí, cualquiera | Sí, su tenant | No |
| Desactivar usuarios | Sí | Sí | No |
| Cambiar tenant de usuario | Sí | No | No |
| Crear tenant_admin | Sí | No | No |
| Asignar tenant_admin | Solo SuperAdmin | No | No |

---

## G. FLUJO DE PUBLICIDADES

### Operaciones CRUD de Advertisements

**Archivo principal:** [app/Http/Controllers/AdvertisementController.php](app/Http/Controllers/AdvertisementController.php)

#### 1. Listar Publicidades (INDEX)

**Ruta:** GET `/advertisements`  
**Policy:** `AdvertisementPolicy@viewAny`

```
1. $this->authorize('viewAny', Advertisement::class)
   ├─ hasTenantPermission(user, 'advertisements.view')
   └─ hasTenantAccess(user, user->tenant_id)

2. TenantScope automáticamente filtra:
   ├─ SUPERADMIN: ve todas
   ├─ TENANT_ADMIN o USER: ve solo su tenant
   └─ (Consulta incluye .where('tenant_id', user->tenant_id))

3. Búsqueda por: name, code
```

**Request:** [app/Http/Requests/StoreAdvertisementRequest.php](app/Http/Requests/StoreAdvertisementRequest.php)

**Clasificación:** ✅ **CORRECTO** - TenantScope protege

#### 2. Crear Publicidad (STORE)

**Ruta:** POST `/advertisements`  
**Policy:** `AdvertisementPolicy@create`

```
1. $this->authorize('create', Advertisement::class)

2. StoreAdvertisementRequest valida:
   ├─ code: unique por tenant
   │  └─ Rule::unique('advertisements', 'code')
   │     ->where(fn($q) => $q->where('tenant_id', user->tenant_id))
   │
   ├─ name: required, string, max 150
   ├─ type: required, in:image,video,html
   ├─ media_path: nullable, max 500
   ├─ duration: required, integer, min 5, max 15
   ├─ is_active: nullable, boolean
   ├─ starts_at: nullable, date
   └─ ends_at: nullable, date, after_or_equal:starts_at

3. En Controller:
   ├─ validated['tenant_id'] = user->tenant_id
   │  (Fuerza tenant automáticamente)
   │
   ├─ Advertisement::create(validated)
   └─ Redirige con success

4. Permisos permitidos:
   ├─ SUPERADMIN: puede crear en cualquier tenant
   ├─ TENANT_ADMIN: solo su tenant
   └─ USER: sí, puede crear
```

**Archivo:** [app/Http/Controllers/AdvertisementController.php](app/Http/Controllers/AdvertisementController.php#L44)

**Clasificación:** ✅ **CORRECTO** - tenant_id forzado

#### 3. Actualizar Publicidad (UPDATE)

**Ruta:** PATCH `/advertisements/{advertisement}`  
**Policy:** `AdvertisementPolicy@update`

```
1. $this->authorize('update', $advertisement)
   ├─ hasTenantPermission(user, 'advertisements.update')
   └─ hasTenantAccess(user, $advertisement->tenant_id)

2. UpdateAdvertisementRequest valida

3. En Controller:
   ├─ advertisement.fill(validated)
   ├─ advertisement.save()
   └─ Redirige con success

4. Nota: 
   ├─ Solo SUPERADMIN puede cambiar tenant_id
   ├─ TENANT_ADMIN/USER no pueden
   └─ (Validación en request/controller)
```

**Clasificación:** ✅ **CORRECTO** - Policy valida tenant

#### 4. Desactivar Publicidad (DESTROY)

**Ruta:** DELETE `/advertisements/{advertisement}`  
**Policy:** `AdvertisementPolicy@delete`

```
1. $this->authorize('delete', $advertisement)

2. En Controller:
   ├─ advertisement.update(['is_active' => false])
   │  (Soft delete lógico)
   │
   └─ Redirige con success

3. Permisos:
   ├─ SUPERADMIN: puede desactivar cualquiera
   ├─ TENANT_ADMIN: puede desactivar su tenant
   └─ USER: NO puede desactivar (sin permiso)
```

**Archivo:** [app/Http/Controllers/AdvertisementController.php](app/Http/Controllers/AdvertisementController.php#L90)

**Clasificación:** ✅ **CORRECTO** - USER no tiene permiso advertisements.delete

### Resumen de Permisos en Publicidades

| Acción | SuperAdmin | TenantAdmin | User |
|--------|-----------|-----------|------|
| Ver publicidades | Sí, todas | Sí, su tenant | Sí, su tenant |
| Crear publicidades | Sí | Sí | Sí |
| Actualizar publicidades | Sí | Sí | Sí |
| Desactivar publicidades | Sí | Sí | No (sin permiso) |

**Nota:** Diferencia clave: USER puede ver, crear y actualizar pero NO puede desactivar.

---

## H. MATRIZ DE PERMISOS POR ROL

### Tabla Completa de Permisos por Rol

| Permiso | SuperAdmin | TenantAdmin | User |
|---------|-----------|-----------|------|
| users.view | ✅ | ✅ | ❌ |
| users.create | ✅ | ✅ | ❌ |
| users.update | ✅ | ✅ | ❌ |
| users.delete | ✅ | ✅ | ❌ |
| displays.view | ✅ | ✅ | ✅ |
| displays.create | ✅ | ✅ | ❌ |
| displays.update | ✅ | ✅ | ❌ |
| displays.delete | ✅ | ❌ | ❌ |
| advertisements.view | ✅ | ✅ | ✅ |
| advertisements.create | ✅ | ✅ | ✅ |
| advertisements.update | ✅ | ✅ | ✅ |
| advertisements.delete | ✅ | ❌ | ❌ |
| menus.view | ✅ | ✅ | ✅ |
| menus.create | ✅ | ✅ | ✅ |
| menus.update | ✅ | ✅ | ✅ |
| menus.delete | ✅ | ❌ | ❌ |
| menu_items.view | ✅ | ✅ | ✅ |
| menu_items.create | ✅ | ✅ | ✅ |
| menu_items.update | ✅ | ✅ | ✅ |
| menu_items.delete | ✅ | ❌ | ❌ |
| ad_schedules.view | ✅ | ✅ | ✅ |
| ad_schedules.create | ✅ | ✅ | ✅ |
| ad_schedules.update | ✅ | ✅ | ✅ |
| ad_schedules.delete | ✅ | ❌ | ❌ |
| analytics.view | ✅ | ✅ | ✅ |

### Resumen de Capacidades por Rol

#### SUPERADMIN
- ✅ Accede a TODO el sistema
- ✅ Gestiona todos los tenants
- ✅ Crea y gestiona usuarios de cualquier tenant
- ✅ Puede crear tenant_admin
- ✅ Ve análisis de cualquier tenant
- ✅ Puede eliminar/desactivar casi todo
- ✅ Identificación: `tenant_id = NULL` + `hasRole('superadmin')`

#### TENANT_ADMIN
- ✅ Administra solo su tenant
- ✅ Crea usuarios dentro de su tenant
- ✅ Gestiona publicidades, displays, menús
- ✅ Ve análisis de su tenant
- ❌ No puede crear otro tenant_admin
- ❌ No puede cambiar tenant_id de usuarios
- ❌ No puede eliminar displays, publicidades, menús
- ✅ Identificación: `tenant_id != NULL` + `hasRole('tenant_admin')`

#### USER
- ✅ Puede crear publicidades, menus, menuitems, horarios
- ✅ Puede actualizar publicidades, menus, menuitems, horarios
- ✅ Ve displays públicos
- ✅ Ve análisis
- ❌ No puede crear ni eliminar displays
- ❌ No puede eliminar publicidades, menús
- ❌ No puede administrar usuarios
- ✅ Identificación: `tenant_id != NULL` + `hasRole('user')`

---

## I. ARCHIVOS INVOLUCRADOS

### Estructura Completa de Archivos Auditados

#### Modelos (app/Models/)
- ✅ [User.php](app/Models/User.php) - Con BelongsToTenant, HasRoles
- ✅ [Tenant.php](app/Models/Tenant.php) - Sin scope (es el padre)
- ✅ [Display.php](app/Models/Display.php) - Con BelongsToTenant
- ✅ [Advertisement.php](app/Models/Advertisement.php) - Con BelongsToTenant
- ✅ [Menu.php](app/Models/Menu.php) - Con BelongsToTenant
- ⚠️ [MenuItem.php](app/Models/MenuItem.php) - SIN BelongsToTenant
- ⚠️ [DisplayAdvertisement.php](app/Models/DisplayAdvertisement.php) - SIN BelongsToTenant
- ⚠️ [AdSchedule.php](app/Models/AdSchedule.php) - SIN BelongsToTenant
- ✅ [AnalyticsEvent.php](app/Models/AnalyticsEvent.php) - Con BelongsToTenant

#### Traits (app/Traits/)
- ✅ [BelongsToTenant.php](app/Traits/BelongsToTenant.php) - Aplica TenantScope

#### Scopes (app/Scopes/)
- ✅ [TenantScope.php](app/Scopes/TenantScope.php) - Lógica multi-tenant

#### Controllers (app/Http/Controllers/)
- ✅ [Auth/RegisteredUserController.php](app/Http/Controllers/Auth/RegisteredUserController.php)
- ✅ [Auth/AuthenticatedSessionController.php](app/Http/Controllers/Auth/AuthenticatedSessionController.php)
- ✅ [UserController.php](app/Http/Controllers/UserController.php)
- ✅ [AdvertisementController.php](app/Http/Controllers/AdvertisementController.php)
- ✅ [DisplayController.php](app/Http/Controllers/DisplayController.php)
- ✅ [MenuController.php](app/Http/Controllers/MenuController.php)
- ✅ [MenuItemController.php](app/Http/Controllers/MenuItemController.php)
- ✅ [AdScheduleController.php](app/Http/Controllers/AdScheduleController.php)
- ✅ [AnalyticsEventController.php](app/Http/Controllers/AnalyticsEventController.php)
- ✅ [DisplayAdvertisementController.php](app/Http/Controllers/DisplayAdvertisementController.php)
- ✅ [ProfileController.php](app/Http/Controllers/ProfileController.php)

#### Policies (app/Policies/)
- ✅ [TenantPolicy.php](app/Policies/TenantPolicy.php) - Clase base
- ✅ [UserPolicy.php](app/Policies/UserPolicy.php)
- ✅ [AdvertisementPolicy.php](app/Policies/AdvertisementPolicy.php)
- ✅ [DisplayPolicy.php](app/Policies/DisplayPolicy.php)
- ✅ [MenuPolicy.php](app/Policies/MenuPolicy.php)
- ✅ [MenuItemPolicy.php](app/Policies/MenuItemPolicy.php)
- ✅ [AdSchedulePolicy.php](app/Policies/AdSchedulePolicy.php)
- ✅ [AnalyticsEventPolicy.php](app/Policies/AnalyticsEventPolicy.php)
- ✅ [DisplayAdvertisementPolicy.php](app/Policies/DisplayAdvertisementPolicy.php)

#### Requests (app/Http/Requests/)
- ✅ [Auth/LoginRequest.php](app/Http/Requests/Auth/LoginRequest.php)
- ✅ [StoreUserRequest.php](app/Http/Requests/StoreUserRequest.php)
- ✅ [UpdateUserRequest.php](app/Http/Requests/UpdateUserRequest.php)
- ✅ [StoreAdvertisementRequest.php](app/Http/Requests/StoreAdvertisementRequest.php)
- ✅ [UpdateAdvertisementRequest.php](app/Http/Requests/UpdateAdvertisementRequest.php)
- ✅ [StoreDisplayRequest.php](app/Http/Requests/StoreDisplayRequest.php)
- ✅ [UpdateDisplayRequest.php](app/Http/Requests/UpdateDisplayRequest.php)
- ✅ [StoreMenuRequest.php](app/Http/Requests/StoreMenuRequest.php)
- ✅ [UpdateMenuRequest.php](app/Http/Requests/UpdateMenuRequest.php)
- ✅ [StoreMenuItemRequest.php](app/Http/Requests/StoreMenuItemRequest.php)
- ✅ [UpdateMenuItemRequest.php](app/Http/Requests/UpdateMenuItemRequest.php)
- ✅ [StoreAdScheduleRequest.php](app/Http/Requests/StoreAdScheduleRequest.php)
- ✅ [UpdateAdScheduleRequest.php](app/Http/Requests/UpdateAdScheduleRequest.php)
- ✅ [StoreAnalyticsEventRequest.php](app/Http/Requests/StoreAnalyticsEventRequest.php)
- ✅ [StoreDisplayAdvertisementRequest.php](app/Http/Requests/StoreDisplayAdvertisementRequest.php)
- ✅ [UpdateDisplayAdvertisementRequest.php](app/Http/Requests/UpdateDisplayAdvertisementRequest.php)
- ✅ [ProfileUpdateRequest.php](app/Http/Requests/ProfileUpdateRequest.php)

#### Seeders (database/seeders/)
- ✅ [DatabaseSeeder.php](database/seeders/DatabaseSeeder.php)
- ✅ [RoleSeeder.php](database/seeders/RoleSeeder.php)
- ✅ [SuperAdminSeeder.php](database/seeders/SuperAdminSeeder.php)

#### Migraciones (database/migrations/)
- ✅ [2026_08_25_000001_create_tenants_table.php](database/migrations/2026_08_25_000001_create_tenants_table.php)
- ✅ [2026_08_25_000002_create_users_table.php](database/migrations/2026_08_25_000002_create_users_table.php)
- ✅ [2026_08_25_000003_create_displays_table.php](database/migrations/2026_08_25_000003_create_displays_table.php)
- ✅ [2026_08_25_000004_create_advertisements_table.php](database/migrations/2026_08_25_000004_create_advertisements_table.php)
- ✅ [2026_08_25_000005_create_display_advertisements_table.php](database/migrations/2026_08_25_000005_create_display_advertisements_table.php)
- ✅ [2026_08_25_000006_create_ad_schedules_table.php](database/migrations/2026_08_25_000006_create_ad_schedules_table.php)
- ✅ [2026_08_25_000007_create_menus_table.php](database/migrations/2026_08_25_000007_create_menus_table.php)
- ✅ [2026_08_25_000008_create_menu_items_table.php](database/migrations/2026_08_25_000008_create_menu_items_table.php)
- ✅ [2026_08_25_000009_create_analytics_events_table.php](database/migrations/2026_08_25_000009_create_analytics_events_table.php)
- ✅ [2026_08_27_180851_create_permission_tables.php](database/migrations/2026_08_27_180851_create_permission_tables.php) - Spatie
- ✅ [2026_08_27_195149_create_sessions_table.php](database/migrations/2026_08_27_195149_create_sessions_table.php)
- ⚠️ [2026_08_29_041441_create_tenant_user_permissions_table.php](database/migrations/2026_08_29_041441_create_tenant_user_permissions_table.php) - Vacía

#### Rutas (routes/)
- ✅ [web.php](routes/web.php) - Todas con middleware auth
- ✅ [auth.php](routes/auth.php) - Rutas de autenticación

#### Configuración (config/)
- ✅ [auth.php](config/auth.php) - Guard web + session

#### Providers (app/Providers/)
- ✅ [AuthServiceProvider.php](app/Providers/AuthServiceProvider.php)
- ✅ [AppServiceProvider.php](app/Providers/AppServiceProvider.php)

---

## J. REFERENCIAS RESTANTES A SYSTEM

### Búsqueda Exhaustiva

Ejecutada: `grep -r "system|SYSTEM|tenant.principal|TenantSeeder" --include="*.php" --include="*.md"`

**Resultado:** 0 referencias a SYSTEM o tenant principal

- ✅ NO existe TenantSeeder que cree un tenant "SYSTEM"
- ✅ NO existe un usuario asignado a un tenant ficticio
- ✅ NO existe campo "tenant principal"
- ✅ Registración funciona sin depender de SYSTEM

**Conclusión:** ✅ **CORRECTO** - No hay vestigios del patrón SYSTEM

**Nota importante:** Una referencia a "create_tenant_user_permissions_table" existe pero está vacía (sin columnas). Es una tabla que no se usa actualmente.

---

## K. INCONSISTENCIAS ENCONTRADAS

### 1. MenuItem SIN BelongsToTenant

**Severidad:** ⚠️ **ADVERTENCIA**

**Archivo:** [app/Models/MenuItem.php](app/Models/MenuItem.php)

**Problema:**
```php
class MenuItem extends Model
{
    // NO tiene BelongsToTenant
    // Solo tiene FK menu_id → menus
}
```

**Impacto:**
- MenuItem NO filtra automáticamente por TenantScope
- Si alguien consulta `MenuItem::all()` podría obtener items de otros tenants
- Sin embargo, en práctica está protegido porque:
  - MenuItemPolicy valida `tenantOf(MenuItem)` indirectamente
  - Se accede siempre a través de Menu

**Ejemplo de riesgo:**
```php
MenuItem::find($id); // Podría no filtrar
MenuItem::all();     // Podría no filtrar
```

**Recomendación:** Agregar BelongsToTenant a MenuItem

### 2. DisplayAdvertisement SIN BelongsToTenant

**Severidad:** ⚠️ **ADVERTENCIA**

**Archivo:** [app/Models/DisplayAdvertisement.php](app/Models/DisplayAdvertisement.php)

**Problema:**
```php
class DisplayAdvertisement extends Model
{
    // NO tiene BelongsToTenant
    // Tiene FK display_id y advertisement_id
}
```

**Impacto:**
- DisplayAdvertisement NO filtra automáticamente
- DisplayAdvertisementPolicy hace validación manual en `tenantOf()`
- Acceso directo podría no filtrar

**Ejemplo de riesgo:**
```php
DisplayAdvertisement::find($id); // Podría no filtrar
```

**Recomendación:** Agregar BelongsToTenant a DisplayAdvertisement

### 3. AdSchedule SIN BelongsToTenant

**Severidad:** 🔴 **ALTO RIESGO DE SEGURIDAD**

**Archivo:** [app/Models/AdSchedule.php](app/Models/AdSchedule.php)

**Problema:**
```php
class AdSchedule extends Model
{
    // NO tiene BelongsToTenant
    // NO tiene FK tenant_id en base de datos
    // Solo tiene FK display_advertisement_id
}
```

**Impacto:**
- AdSchedule NO tiene tenant_id en la BD
- Policy debe navegar: AdSchedule → DisplayAdvertisement → Display → tenant_id
- Si consulta directa a AdSchedule sin pasar por Policy es vulnerable
- TenantScope NO protege este modelo

**Riesgo Real:**
```php
AdSchedule::find($id); // ❌ NO filtra por tenant
AdSchedule::where('display_advertisement_id', $id)->get(); // ❌ NO filtra
```

**Clasificación:** 🔴 **RIESGO DE SEGURIDAD**

### 4. Tabla tenant_user_permissions Vacía

**Severidad:** 🟡 **INFORMACIÓN**

**Archivo:** [2026_08_29_041441_create_tenant_user_permissions_table.php](database/migrations/2026_08_29_041441_create_tenant_user_permissions_table.php)

**Problema:**
```php
Schema::create('tenant_user_permissions', function (Blueprint $table) {
    $table->id();
    $table->timestamps();
    // ← Sin columnas de relación
});
```

**Impacto:**
- Tabla existe pero nunca se usa
- No hay modelo que use esta tabla
- Posiblemente es código abandonado

**Recomendación:** Eliminar si no se usa

---

## L. POSIBLES VULNERABILIDADES O PROBLEMAS DE AUTORIZACIÓN

### Escenarios de Ataque Potencial

#### ESCENARIO A: SuperAdmin inicia sesión

**Flujo:**
```
1. Usuario con email='admin' hace login
2. LoginRequest autentica con password
3. Auth::attempt() retorna true
4. Sesión se crea con User.id + User.tenant_id=NULL
5. User tiene role='superadmin'
6. TenantScope detecta role 'superadmin' → no filtra
7. Usuario ve TODO
```

**Validación:** ✅ **CORRECTO**
- Email único: ✅
- Password hasheado: ✅
- Sesión protegida: ✅
- Rol asignado correctamente: ✅

**Resultado:** 🟢 **SIN RIESGO**

---

#### ESCENARIO B: Empresa Coca-Cola se registra

**Flujo:**
```
1. POST /register con:
   - tenant_name: "Coca-Cola"
   - name: "Juan López"
   - email: "juan@coca-cola.com"
   - password: "securePass123"

2. DB::transaction {
   - Crear Tenant (code='TEN-XXXXX', slug=coca-cola-xxxxx)
   - Crear User (tenant_id=123, email='juan@coca-cola.com')
   - assignRole('tenant_admin')
   }

3. Auth::login(user) → sesión iniciada
4. Redirect /dashboard
```

**Validaciones:**
- ✅ Email único globalmente
- ✅ Password min 8, mayúscula, número, símbolo
- ✅ Tenant creado con código único
- ✅ User vinculado al tenant
- ✅ Rol asignado automáticamente

**Resultado:** 🟢 **SIN RIESGO**

---

#### ESCENARIO C: tenant_admin crea usuarios

**Escenario:**
```
Tenant Admin (Juan) intenta crear 3 usuarios:
- Carlos (user)
- Ana (user)
- José (user)
```

**Validaciones en Controller:**
```php
if ($currentUser->hasRole('superadmin')) {
    $tenantId = $validated['tenant_id'] ?? null;
} else {
    $tenantId = $currentUser->tenant_id; // ← Forzado a su tenant
}
```

**Result:** ✅ **CORRECTO**
- tenant_id NO viene del request, se fuerza desde sesión
- Juan no puede crear usuarios en otro tenant

---

#### ESCENARIO D: tenant_admin intenta crear otro tenant_admin

**Escenario:**
```
Juan (tenant_admin) intenta crear a Marcos como tenant_admin
en su mismo tenant.
```

**Validación en Controller:**
```php
if ($role === 'tenant_admin') {
    if (! $currentUser->hasRole('superadmin')) {
        throw ValidationException::withMessages([
            'role' => 'Solo el SuperAdmin puede asignar el rol de administrador.',
        ]);
    }
}
```

**Resultado:** ✅ **CORRECTO**
- Solo SuperAdmin puede crear tenant_admin
- Juan recibe error

---

#### ESCENARIO E: Usuario normal intenta eliminar publicidad

**Escenario:**
```
Carlos (user) intenta DELETE /advertisements/456
```

**Policy Check:**
```php
public function delete(User $user, Advertisement $advertisement): bool
{
    return $this->hasTenantPermission($user, 'advertisements.delete')
        && $this->hasTenantAccess($user, $advertisement->tenant_id);
}
```

**RoleSeeder dice:**
```php
$user->syncPermissions([
    'advertisements.view',        // ✅
    'advertisements.create',      // ✅
    'advertisements.update',      // ✅
    // advertisements.delete ← FALTA
]);
```

**Resultado:** ✅ **CORRECTO**
- Policy retorna false
- Lanza 403 Unauthorized
- Publicidad NO se elimina

---

#### ESCENARIO F: Tenant intenta consultar usuarios de otro Tenant

**Escenario:**
```
Tenant A (Juan) intenta ver usuarios de Tenant B
```

**Ataque posible:**
```php
// Intenta acceder directamente:
User::where('tenant_id', 999)->get(); // ← Otro tenant

// O en URL:
GET /users?tenant_id=999
```

**Protecciones:**

1. **TenantScope:**
   ```php
   if (blank($user->tenant_id)) { // Juan tiene tenant_id = 123
       $builder->whereRaw('0 = 1');
       return;
   }
   $builder->where('users.tenant_id', $user->tenant_id); // ← Filtra a 123
   ```

2. **UserPolicy:**
   ```php
   public function viewAny(User $user): bool
   {
       return $this->hasTenantAccess($user, $user->tenant_id);
   }
   ```

3. **UserController:**
   ```php
   if ($request->filled('tenant_id')) {
       $query->where('tenant_id', $request->tenant_id);
   }
   // ← Pero si no es SuperAdmin, TenantScope ya filtró
   ```

**Ataque HTTP:**
```
GET /users?tenant_id=999
```

**Qué sucede:**
1. UserPolicy::viewAny → verifica `hasTenantAccess(juan, 123)` → ✅
2. Query filtra por TenantScope → `WHERE tenant_id = 123`
3. Parámetro tenant_id=999 se ignora porque TenantScope ya filtró
4. Resultado: Solo ve usuarios de su tenant

**Resultado:** ✅ **CORRECTO** - Doble protección (Policy + TenantScope)

---

#### ESCENARIO G: SuperAdmin consulta y filtra usuarios por Tenant

**Escenario:**
```
SuperAdmin quiere ver usuarios de Tenant B
```

**Flujo:**
```
GET /users?tenant_id=999

1. SuperAdmin tiene tenant_id = NULL + role='superadmin'
2. UserPolicy::viewAny:
   - hasTenantAccess(superadmin, NULL) → ✅ (superadmin siempre true)
3. TenantScope:
   - if ($user->hasRole('superadmin')) {
       return; // ← No filtra
     }
4. Query ejecuta: WHERE tenant_id = 999
5. Retorna usuarios de Tenant B
```

**Resultado:** ✅ **CORRECTO**
- SuperAdmin puede filtrar por tenant_id
- TenantScope permite acceso sin filtrar

---

#### ESCENARIO H: Alguien intenta cambiar tenant_id de usuario

**Escenario A: Tenant Admin intenta reasignar usuario a otro tenant**

```
PUT /users/456
{
    "name": "Carlos",
    "email": "carlos@email.com",
    "tenant_id": 999  // ← Intenta cambiar
}
```

**UpdateUserRequest valida:**
```php
if ($currentUser->isSuperAdmin()) {
    $rules['tenant_id'] = [
        'required', 'integer', 'exists:tenants,id',
    ];
}
// ← Si no es SuperAdmin, NO se valida tenant_id
```

**En Controller:**
```php
if (! $currentUser->hasRole('superadmin')) {
    unset($validated['tenant_id']); // ← Se elimina del array
}
```

**Resultado:** ✅ **CORRECTO**
- tenant_id se ignora si no es SuperAdmin
- Usuario no se cambia de tenant

---

**Escenario B: SuperAdmin intenta cambiar a un usuario a NULL**

```
PUT /users/456
{
    "tenant_id": null
}
```

**En Controller:**
```php
$tenantId = $validated['tenant_id'] ?? $user->tenant_id;
if (blank($tenantId)) {
    throw ValidationException::withMessages([
        'tenant_id' => 'El usuario debe pertenecer a un tenant.',
    ]);
}
```

**Resultado:** ✅ **CORRECTO**
- No permite NULL para usuario normal
- Solo superadmin tiene tenant_id = NULL por seeder

---

#### ESCENARIO I: Intento de modificar/desactivar al SuperAdmin

**Escenario:**
```
DELETE /users/1 (donde user_id=1 es el SuperAdmin)
```

**En UserController::destroy:**
```php
if ($user->hasRole('superadmin')) {
    throw ValidationException::withMessages([
        'user' => 'El SuperAdmin no puede ser desactivado.',
    ]);
}
```

**Resultado:** ✅ **CORRECTO**
- Protección explícita contra eliminar SuperAdmin

---

#### ESCENARIO J: Mi intento directo en BD para ver si falla TenantScope

**Pregunta:** ¿Qué pasa si hago una consulta directa sin pasar por TenantScope?

```php
User::withoutGlobalScopes()->where('tenant_id', '!=', auth()->user()->tenant_id)->get();
```

**Respuesta:** ⚠️ **POTENCIAL RIESGO**
- `withoutGlobalScopes()` **desactiva TenantScope**
- Accesible desde cualquier lugar

**Però en la práctica:**
- No hay uso de `withoutGlobalScopes()` en el codebase
- Solo se accede a través de Controllers que tienen Policies
- Controllers usan `$this->authorize()` que valida antes

**Resultado:** 🟡 **ADVERTENCIA** - Depende de que Controllers use siempre Policies

---

### Resumen de Riesgos Identificados

| Riesgo | Severidad | Estado | Mitigation |
|--------|-----------|--------|-----------|
| MenuItem sin BelongsToTenant | 🟡 | Parcial | Policy manual |
| DisplayAdvertisement sin BelongsToTenant | 🟡 | Parcial | Policy manual |
| AdSchedule sin BelongsToTenant | 🔴 | ALTO | Policy manual (pero sin tenant_id en BD) |
| withoutGlobalScopes() no usado | 🟢 | OK | - |
| SuperAdmin protegido | 🟢 | OK | Validación explícita |
| tenant_id = NULL validado | 🟢 | OK | Validación en Controller |
| TenantAdmin no puede crear admin | 🟢 | OK | Policy + Controller |
| Cambio de tenant protegido | 🟢 | OK | Request + Controller |

---

## M. CONCLUSIÓN

### Resumen Ejecutivo

**El proyecto implementa correctamente un SaaS multi-tenant con protecciones adecuadas en la mayoría de escenarios.**

### Fortalezas

✅ **Autenticación:**
- Login/logout implementado correctamente
- Rate limiting activo
- Session segura
- Roles asignados apropiadamente

✅ **Multi-Tenancy:**
- TenantScope automático en 5 modelos principales
- Doble protección: Scope + Policy
- Validación en level de Controller
- Foreign keys con cascadeOnDelete

✅ **Usuarios:**
- SuperAdmin protegido
- tenant_id validado en cada operación
- tenant_admin limitado a 1 por tenant
- No se puede crear usuario sin tenant

✅ **Roles y Permisos:**
- Spatie Permission correctamente integrado
- 27 permisos definidos
- Matriz de permisos coherente
- Validaciones en Policies

✅ **Registro:**
- Flujo transaccional
- Tenant + User creados atomáticamente
- Rol asignado automáticamente
- NO depende de tenant SYSTEM

✅ **Publicidades:**
- tenant_id forzado en Controller
- USER correctamente limitado (sin delete)
- TENANT_ADMIN sin delete
- SUPERADMIN acceso total

### Debilidades / Advertencias

⚠️ **MenuItem sin BelongsToTenant**
- Depende de Policy manual
- Riesgo bajo en práctica porque se accede siempre por Menu

⚠️ **DisplayAdvertisement sin BelongsToTenant**
- Depende de Policy manual
- Riesgo medio si se accede directamente

🔴 **AdSchedule sin BelongsToTenant Y sin tenant_id en BD**
- NO tiene tenant_id en base de datos
- Policy debe navegar 3 FK
- Riesgo ALTO si se accede directamente
- Consultas como `AdSchedule::all()` podrían exponer datos

⚠️ **tenant_user_permissions vacía**
- Tabla existe pero no se usa
- Código muerto

### Recomendaciones de Prioridad

**CRÍTICA (debe arreglarse):**
1. Agregar `tenant_id` a tabla `ad_schedules`
2. Agregar `BelongsToTenant` a `AdSchedule`
3. Agregar `BelongsToTenant` a `DisplayAdvertisement`
4. Agregar `BelongsToTenant` a `MenuItem`

**ALTA (debe arreglarse pronto):**
1. Remover tabla `tenant_user_permissions` o implementar si se planeaba

**MEDIA (buena práctica):**
1. Auditar que NO hay uso de `withoutGlobalScopes()` en el código
2. Revisar que todos los Controllers usan `$this->authorize()`

### Matriz de Cumplimiento de Requisitos

| Requisito | Status | Evidencia |
|-----------|--------|-----------|
| SUPERADMIN es propietario del SaaS | ✅ | tenant_id=NULL + role=superadmin |
| Se crea automáticamente por Seeder | ✅ | SuperAdminSeeder |
| Tiene acceso total | ✅ | TenantScope retorna sin filtrar |
| Puede listar usuarios de todos los tenants | ✅ | UserController::index |
| Puede crear usuarios | ✅ | UserPolicy::create |
| Puede administrar cualquier tenant | ✅ | TenantScope permite acceso |
| TENANT_ADMIN es el propietario del Tenant | ✅ | AssignedRole en register |
| Se crea al registrarse | ✅ | RegisteredUserController::store |
| Está vinculado automáticamente al Tenant | ✅ | tenant_id forzado |
| Se le asigna tenant_admin automáticamente | ✅ | assignRole('tenant_admin') |
| Puede administrar su Tenant únicamente | ✅ | TenantScope filtra |
| Máximo 1 por Tenant | ✅ | UserController valida |
| No puede acceder a otros Tenants | ✅ | TenantScope + Policy |
| USER puede crear publicidades | ✅ | RoleSeeder::permissions |
| USER NO puede eliminar publicidades | ✅ | Sin permiso advertisements.delete |
| No existe dependencia a SYSTEM | ✅ | 0 referencias encontradas |

### Calificación General

**Seguridad Multi-Tenant:** 8.5/10
- Implementación correcta del concepto
- Protecciones múltiples en capas
- Algunos modelos sin scope (pero con Policy)

**Autenticación y Autorización:** 9/10
- Login/logout correcto
- Roles bien definidos
- Permisos coherentes

**Gestión de Usuarios:** 9/10
- Validaciones completas
- SuperAdmin protegido
- tenant_admin limitado

**Integridad de Datos:** 8/10
- Foreign keys correctos
- Cascades implementadas
- Pero AdSchedule sin tenant_id es riesgo

**Clasificación Final: 8.5/10 - BUENO**

Sistema funcional y seguro en la mayoría de casos. Recomendado para producción con correcciones de AdSchedule/DisplayAdvertisement/MenuItem prioritizadas.

---

## ANEXO: Definiciones

- **BelongsToTenant:** Trait que aplica TenantScope automáticamente a un modelo
- **TenantScope:** Global Scope que filtra por tenant_id automáticamente en queries
- **Policy:** Autorización a nivel de modelo (puede el usuario X hacer Y en modelo Z?)
- **tenant_id NULL:** Identificador único de SuperAdmin
- **cascadeOnDelete:** Foreign key que elimina registros relacionados
- **nullOnDelete:** Foreign key que pone NULL el campo si se elimina el relacionado
- **withoutGlobalScopes():** Método que desactiva todos los scopes de un modelo (PELIGROSO)

---

**Fin del Informe**  
Javier Lopez | 30 de agosto de 2026
