# CONTEXTO DEL PROYECTO — TOTEMS SAAS

Este archivo contiene las reglas y contexto principal del proyecto.

IMPORTANTE:
Antes de modificar cualquier archivo, inspecciona primero el proyecto existente.

Debes revisar como mínimo:

- Migraciones
- Modelos
- Relaciones Eloquent
- Policies
- TenantScope
- BelongsToTenant
- Seeders
- Breeze/Auth
- Rutas existentes
- Controllers existentes
- Form Requests existentes
- Configuración de Inertia
- package.json
- composer.json

NO empieces a modificar código hasta comprender la estructura existente.

Si encuentras una inconsistencia, NO la corrijas automáticamente.
Primero informa:
- archivo
- problema
- posible solución

==================================================
STACK TECNOLÓGICO
==================================================

Backend:
- Laravel 12
- PHP 8.3
- MySQL
- Eloquent ORM

Frontend:
- React
- Inertia.js
- Tailwind CSS

Autenticación:
- Laravel Breeze
- Session Auth

Roles y permisos:
- Spatie laravel-permission

Multi-tenancy:
- Row-level tenancy
- tenant_id
- TenantScope
- BelongsToTenant
- Laravel Policies

NO instalar paquetes adicionales.

NO cambiar la arquitectura existente.

NO cambiar nombres de:
- tablas
- columnas
- modelos
- relaciones

NO eliminar archivos existentes.

NO ejecutar:
- migrate:fresh
- migrate:refresh
- db:wipe

NO eliminar datos existentes de la base de datos.

NO modificar React mientras la tarea sea exclusivamente Backend.

==================================================
MODELOS PRINCIPALES
==================================================

Modelos:

- Tenant
- User
- Display
- Advertisement
- DisplayAdvertisement
- AdSchedule
- Menu
- MenuItem
- AnalyticsEvent

Relaciones esperadas:

Tenant:
- users
- displays
- advertisements
- menus
- analyticsEvents

User:
- tenant

Advertisement:
- tenant
- displayAdvertisements
- analyticsEvents

Display:
- tenant
- displayAdvertisements
- menus
- analyticsEvents

DisplayAdvertisement:
- display
- advertisement
- adSchedules

AdSchedule:
- displayAdvertisement

Menu:
- tenant
- display
- items

MenuItem:
- menu
- parent
- children

AnalyticsEvent:
- tenant
- display
- advertisement
- menuItem

IMPORTANTE:

Las relaciones anteriores son solamente una referencia.

Siempre revisar primero los modelos reales.

No crear relaciones duplicadas ni asumir relaciones que no existan.

==================================================
ROLES Y PERMISOS
==================================================

Roles:

- superadmin
- tenant_admin
- user

superadmin:
- acceso global
- puede administrar todos los tenants
- puede realizar todas las operaciones permitidas

tenant_admin:
- administra recursos de su tenant
- puede visualizar
- puede crear
- puede actualizar
- no puede eliminar si no posee el permiso correspondiente

user:
- acceso limitado a los recursos permitidos
- solamente dentro de su tenant
- actualmente no posee permisos de eliminación

Los permisos son administrados mediante:

Spatie laravel-permission

NO implementar comprobaciones manuales de roles si una Policy existente ya controla esa operación.

Utilizar:

- Policies
- authorize()
- $this->authorize()
- can()
- @can en frontend cuando corresponda

==================================================
MULTI-TENANCY
==================================================

El sistema utiliza row-level multi-tenancy mediante:

tenant_id

Regla principal:

Un usuario normal solamente puede acceder a información perteneciente a su tenant.

superadmin puede acceder globalmente.

Respetar siempre:

- TenantScope
- BelongsToTenant
- Policies

NUNCA confiar en tenant_id enviado desde React/Inertia.

Cuando se cree un recurso perteneciente a un tenant:

- utilizar el tenant del usuario autenticado
- nunca permitir que un usuario normal seleccione arbitrariamente otro tenant

Nunca permitir que un usuario normal:

- consulte información de otro tenant
- cree recursos para otro tenant
- actualice recursos de otro tenant
- elimine recursos de otro tenant

Evitar cualquier fuga de información entre tenants.

==================================================
CONTROLLERS
==================================================

Los Controllers deben ser delgados.

Responsabilidades:

- recibir la petición
- autorizar
- llamar al modelo/query
- devolver respuesta Inertia o redirect
- manejar correctamente errores de validación

NO colocar grandes cantidades de lógica de negocio dentro de Controllers.

Utilizar:

- Form Requests para validación
- Policies para autorización
- Eloquent para relaciones y consultas

Para operaciones protegidas utilizar Policies.

Ejemplo conceptual:

$this->authorize('update', $advertisement);

==================================================
FORM REQUESTS
==================================================

Crear Form Requests separados cuando corresponda:

- StoreXRequest
- UpdateXRequest

Ejemplo:

StoreAdvertisementRequest
UpdateAdvertisementRequest

Las validaciones deben:

- impedir datos inválidos
- impedir duplicidades
- validar tipos de datos
- validar relaciones existentes
- validar fechas
- validar valores permitidos
- devolver mensajes claros en español

Los mensajes de validación deben ayudar a identificar fácilmente el problema.

No confiar únicamente en las validaciones del frontend.

La validación importante siempre debe existir en Backend.

==================================================
DUPLICIDADES
==================================================

Al crear recursos se deben evitar duplicidades cuando corresponda.

Antes de definir una regla unique:

1. Revisar la migración.
2. Revisar índices únicos existentes.
3. Revisar reglas de negocio.
4. Determinar si la unicidad es global o por tenant.

Ejemplo:

Un código puede ser único globalmente.

Un nombre podría necesitar ser único solamente dentro del tenant.

NO asumir la regla.
Revisar primero la estructura existente.

==================================================
GET / CONSULTAS
==================================================

Las consultas deben devolver información útil para el frontend.

Cuando un recurso tiene relaciones importantes, cargar las relaciones necesarias mediante Eloquent.

Utilizar:

- with()
- load()
- withCount()
- whereHas()
- whereRelation()

Evitar problemas N+1.

Ejemplo conceptual:

Display::with([
    'displayAdvertisements.advertisement',
    'displayAdvertisements.adSchedules'
])

Las respuestas deben incluir la información relacionada cuando sea necesaria para representar correctamente el recurso.

==================================================
BÚSQUEDAS
==================================================

Las búsquedas destinadas al usuario final deben ser intuitivas.

Preferir búsquedas por campos comprensibles como:

- nombre
- código
- slug

Ejemplo:

GET de publicidades:

- buscar por nombre
- buscar por código
- filtrar por estado

GET de displays:

- buscar por nombre
- buscar por código
- filtrar por estado

No utilizar únicamente IDs como mecanismo de búsqueda para la interfaz.

Los IDs siguen siendo importantes internamente y para relaciones.

==================================================
RELACIONES EN GET
==================================================

Cuando un recurso tenga relaciones relevantes, comprobar que la información retornada corresponda realmente al recurso solicitado.

Ejemplos:

Display:
- sus anuncios asociados
- información de Advertisement
- programación relacionada cuando corresponda

Advertisement:
- displays asociados
- programación relacionada cuando corresponda

Menu:
- items
- relaciones de los items cuando corresponda

MenuItem:
- menu
- parent
- children

AdSchedule:
- DisplayAdvertisement
- Display
- Advertisement

Nunca devolver información de otro tenant.

==================================================
UPDATE
==================================================

Los registros se identificarán internamente mediante:

- ID

No utilizar el nombre como identificador principal para actualizar.

El nombre puede utilizarse para buscar.

Para editar:

- obtener el registro mediante ID
- ejecutar Policy
- validar mediante UpdateRequest
- actualizar únicamente los campos permitidos

Ejemplo conceptual:

PUT /advertisements/{advertisement}

==================================================
DELETE
==================================================

El sistema debe utilizar borrado lógico cuando corresponda.

NO eliminar físicamente información importante.

Antes de implementar SoftDeletes:

- revisar si la tabla actual tiene deleted_at
- si no existe, reportar que se necesita una modificación de migración

Cuando exista SoftDeletes:

delete()
→ deshabilita/elimina lógicamente el registro

No utilizar:

forceDelete()

salvo que la tarea lo solicite explícitamente.

==================================================
ESTADOS
==================================================

Cuando un recurso tenga:

is_active
status

respetar la estructura existente.

No crear nuevos campos de estado sin revisar primero la migración y modelo.

Para deshabilitar un recurso preferir el mecanismo definido por la estructura actual.

==================================================
RESPUESTAS INERTIA
==================================================

El proyecto utiliza:

React + Inertia.js

Los Controllers deben devolver:

Inertia::render()

cuando corresponda mostrar una página.

Después de:

- crear
- actualizar
- eliminar/deshabilitar

preferir redirect()->back() o redirect()->route(...)

según el flujo existente.

Utilizar flash messages para comunicar éxito o error al frontend cuando corresponda.

Ejemplo conceptual:

return redirect()
    ->route('advertisements.index')
    ->with('success', 'Publicidad creada correctamente.');

Los errores de Form Request deben ser compatibles con Inertia.

==================================================
AUTHENTICATION
==================================================

La autenticación utiliza Laravel Breeze.

No implementar un sistema de login personalizado si Breeze ya proporciona la funcionalidad necesaria.

Respetar:

- login
- logout
- register
- session authentication
- middleware auth

El usuario autenticado debe estar disponible mediante:

Auth::user()

o:

$request->user()

==================================================
AUTORIZACIÓN
==================================================

La autorización se divide en dos niveles:

1. Roles y permisos
2. Tenant isolation

Spatie determina qué puede hacer el usuario.

Policies determinan si puede realizar la acción sobre un recurso específico.

TenantScope/BelongsToTenant ayudan a impedir acceso entre tenants.

No mezclar estas responsabilidades innecesariamente.

==================================================
SEGURIDAD
==================================================

Nunca confiar en datos provenientes del frontend.

Especialmente:

- tenant_id
- user_id
- permisos
- roles
- ownership

El frontend solamente solicita una operación.

El backend decide si puede ejecutarse.

==================================================
ESTILO DE CÓDIGO
==================================================

Seguir convenciones de Laravel.

Utilizar:

- nombres descriptivos
- métodos pequeños
- type hints
- return types cuando corresponda
- Form Requests
- Policies
- Eloquent relationships
- route model binding

Evitar:

- código duplicado
- consultas SQL innecesarias
- lógica compleja en Controllers
- validaciones duplicadas
- comentarios innecesarios

==================================================
PROCESO DE TRABAJO DE COPILOT
==================================================

Antes de modificar código:

1. Inspeccionar archivos relacionados.
2. Identificar dependencias.
3. Revisar migración.
4. Revisar modelo.
5. Revisar relaciones.
6. Revisar Policy.
7. Revisar rutas.
8. Revisar código existente.
9. Proponer qué archivos serán modificados.
10. Implementar solamente después de comprender el contexto.

Después de modificar:

1. Revisar errores de sintaxis.
2. Revisar imports.
3. Revisar nombres de clases.
4. Revisar relaciones.
5. Revisar Policies.
6. Revisar validaciones.
7. Revisar posibles problemas de tenant isolation.
8. Ejecutar pruebas o comandos de verificación seguros cuando corresponda.

NO ejecutar comandos destructivos.

==================================================
REGLA PRINCIPAL
==================================================

No asumir.

Primero inspeccionar el proyecto real.

Utilizar este archivo como contexto general, pero considerar siempre el código existente como fuente definitiva de verdad.