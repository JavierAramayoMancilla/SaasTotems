# 03. Requisitos e Historias de Usuario

## 1. Introducción

El presente documento define los requisitos funcionales y no funcionales del sistema SaaS para la gestión de **tótems publicitarios interactivos**.

El sistema permitirá a diferentes empresas o clientes administrar sus propios displays, publicidades, menús, horarios de reproducción y estadísticas desde una plataforma centralizada.

La plataforma utilizará un modelo de **multi-tenancy basado en `tenant_id`**, permitiendo que múltiples empresas utilicen el mismo sistema manteniendo sus datos aislados.

El desarrollo se organiza mediante **Épicas (E)** e **Historias de Usuario (HU)**, distribuidas en cinco Sprints principales.

---

# 2. Actores del Sistema

## 2.1 SuperAdmin

Es el administrador global de la plataforma SaaS.

Tiene acceso a todos los tenants y puede administrar la configuración general de la plataforma.

### Responsabilidades

* Administrar los clientes registrados.
* Administrar usuarios.
* Gestionar roles y permisos.
* Consultar información global de la plataforma.
* Supervisar displays, publicidades y demás recursos.
* Acceder a estadísticas generales.
* Administrar usuarios pertenecientes a cualquier tenant.

### Restricción

El SuperAdmin no pertenece a ningún tenant específico.

```text
tenant_id = NULL
```

---

## 2.2 Tenant Admin

Es el administrador de una empresa cliente que utiliza el servicio.

Por ejemplo:

```text
Empresa: Coca-Cola
Usuario: coca-cola@gmail.com
Rol: tenant_admin
Tenant: Coca-Cola
```

### Responsabilidades

* Administrar usuarios de su empresa.
* Crear y administrar displays.
* Crear y administrar publicidades.
* Asociar publicidades a displays.
* Crear y administrar menús.
* Programar publicidades.
* Consultar estadísticas de su empresa.

### Restricciones

* Solo puede acceder a información de su propio tenant.
* No puede acceder a información de otros clientes.
* No puede crear otro `tenant_admin` dentro de su tenant.
* No puede modificar el `tenant_id` de los usuarios.

---

## 2.3 User

Es un usuario perteneciente a una empresa cliente.

Puede utilizar las funcionalidades permitidas por sus permisos.

### Responsabilidades

Dependiendo de sus permisos puede:

* Consultar displays.
* Consultar publicidades.
* Crear o modificar contenido.
* Administrar menús.
* Consultar estadísticas.

### Restricciones

* Solo puede acceder a información de su tenant.
* No tiene acceso a funcionalidades exclusivas del SuperAdmin.
* No puede administrar otros tenants.

---

## 2.4 Display / Tótem

Es el dispositivo físico donde se reproduce el contenido publicitario e interactivo.

El display consume la información proporcionada por la plataforma para mostrar:

* Publicidades.
* Menús.
* Contenido multimedia.
* Información programada.

El display forma parte de un tenant determinado.

---

# 3. Modelo General de Acceso

La arquitectura de seguridad utiliza tres niveles principales:

```text
                    PLATAFORMA SaaS
                           │
             ┌─────────────┴─────────────┐
             │                           │
        SUPERADMIN                  CLIENTES
             │                           │
      Acceso global             ┌────────┴────────┐
                                │                 │
                           TENANT ADMIN          USER
                                │                 │
                                └────────┬────────┘
                                         │
                                      TENANT
                                         │
                         ┌───────────────┼───────────────┐
                         │               │               │
                      Displays      Publicidades      Menús
```

El aislamiento de información se implementa mediante:

* `tenant_id`.
* Global Scopes.
* Policies.
* Roles.
* Permisos.
* Validaciones en Requests.
* Relaciones entre modelos.

---

# 4. Épicas del Sistema

Las funcionalidades se agrupan en cinco grandes épicas:

| Épica | Nombre                    | Historias                  |
| ----- | ------------------------- | -------------------------- |
| E1    | Plataforma y arquitectura | HU-01, HU-02               |
| E2    | Usuarios y seguridad      | HU-03, HU-04, HU-05, HU-06 |
| E3    | Publicidad y Displays     | HU-07, HU-08, HU-09, HU-10 |
| E4    | Menús y programación      | HU-11, HU-12, HU-13        |
| E5    | Analítica y experiencia   | HU-14, HU-15, HU-16        |

---

# 5. Historias de Usuario

# E1 — Plataforma y Arquitectura

## HU-01 — Configuración de la arquitectura

**Como desarrollador**, quiero configurar la arquitectura del sistema para disponer de un backend Laravel 12 y un frontend React + Inertia.js.

### Descripción

El sistema debe contar con una arquitectura web moderna que permita separar las responsabilidades del backend y frontend.

### Tecnologías principales

* Laravel 12.
* PHP 8.3.
* React.
* TypeScript.
* Inertia.js.
* Tailwind CSS.
* MySQL.
* Vite.

### Criterios de aceptación

* [ ] El proyecto utiliza Laravel 12.
* [ ] El backend funciona correctamente.
* [ ] React está integrado mediante Inertia.js.
* [ ] Tailwind CSS está configurado.
* [ ] Vite permite compilar los recursos frontend.
* [ ] Laravel puede comunicarse correctamente con MySQL.
* [ ] La aplicación puede ejecutarse en entorno local.

### Sprint

**Sprint 1 — Base de la plataforma**

### Prioridad

**Alta**

---

## HU-02 — Modelo de datos principal

**Como sistema**, quiero almacenar y relacionar correctamente la información de tenants, usuarios, displays, publicidades y menús.

### Descripción

La plataforma debe disponer de una base de datos relacional que permita representar correctamente las entidades principales del sistema.

### Entidades principales

```text
Tenant
   │
   ├── Users
   ├── Displays
   ├── Advertisements
   └── Menus
```

Además, existen relaciones entre:

```text
Displays
    │
    └── DisplayAdvertisements
              │
              ├── Advertisement
              └── AdSchedule
```

### Criterios de aceptación

* [ ] Las tablas principales existen.
* [ ] Las claves primarias están correctamente definidas.
* [ ] Las claves foráneas están configuradas.
* [ ] Las relaciones Eloquent están implementadas.
* [ ] Los datos pertenecientes a cada tenant pueden identificarse correctamente.
* [ ] Las restricciones de integridad referencial funcionan.

### Sprint

**Sprint 1**

### Prioridad

**Alta**

---

# E2 — Usuarios y Seguridad

## HU-03 — Autenticación

**Como usuario**, quiero iniciar y cerrar sesión para acceder de forma segura al sistema.

### Funcionalidades

* Login.
* Logout.
* Registro.
* Manejo de sesiones.
* Validación de credenciales.
* Protección de rutas.

### Criterios de aceptación

* [ ] Un usuario puede iniciar sesión con credenciales válidas.
* [ ] Las credenciales incorrectas son rechazadas.
* [ ] Un usuario autenticado puede cerrar sesión.
* [ ] Las rutas protegidas requieren autenticación.
* [ ] Las sesiones se gestionan de forma segura.
* [ ] Los usuarios inactivos no deben utilizar funcionalidades protegidas.

### Tecnologías

* Laravel Breeze.
* Laravel Authentication.
* Sessions.

### Sprint

**Sprint 2 — Autenticación y seguridad**

### Prioridad

**Alta**

---

## HU-04 — Gestión de usuarios

**Como cliente-administrador**, quiero gestionar los usuarios de mi empresa para controlar quién puede utilizar el servicio.

### Funcionalidades

* Listar usuarios.
* Crear usuarios.
* Consultar usuarios.
* Editar usuarios.
* Desactivar usuarios.
* Asignar roles.
* Buscar usuarios.
* Filtrar usuarios.

### Regla principal

Un `tenant_admin` solo puede administrar usuarios pertenecientes a su propio tenant.

```text
Coca-Cola
    │
    ├── Administrador
    ├── Usuario 1
    ├── Usuario 2
    └── Usuario 3
```

No puede administrar:

```text
Pepsi
    ├── Administrador
    └── Usuarios
```

### Criterios de aceptación

* [ ] El administrador puede crear usuarios.
* [ ] Puede modificar usuarios de su tenant.
* [ ] Puede desactivar usuarios.
* [ ] No puede acceder a usuarios de otros tenants.
* [ ] Solo puede existir un `tenant_admin` por tenant.
* [ ] El SuperAdmin puede administrar usuarios globalmente.
* [ ] El SuperAdmin no puede ser desactivado.

### Sprint

**Sprint 2**

### Prioridad

**Alta**

---

## HU-05 — Roles y permisos

**Como sistema**, quiero utilizar roles y permisos para controlar las operaciones que puede realizar cada usuario.

### Roles principales

```text
superadmin
tenant_admin
user
```

### SuperAdmin

Tiene acceso total a la plataforma.

### Tenant Admin

Tiene permisos administrativos dentro de su tenant.

### User

Tiene permisos limitados según las operaciones asignadas.

### Tecnología

Se utiliza:

**Spatie Laravel Permission**

para gestionar:

* Roles.
* Permisos.
* Asignación de permisos.
* Verificación de permisos.

### Criterios de aceptación

* [ ] Los roles existen.
* [ ] Los permisos están registrados.
* [ ] Los permisos están asociados a los roles.
* [ ] Las Policies verifican permisos.
* [ ] El SuperAdmin tiene acceso global.
* [ ] El Tenant Admin está limitado a su tenant.
* [ ] El User no puede ejecutar operaciones que no tenga autorizadas.

### Sprint

**Sprint 2**

### Prioridad

**Alta**

---

## HU-06 — Aislamiento Multi-Tenant

**Como cliente**, quiero que mi información esté separada de la información de otros clientes para garantizar la privacidad de mis datos.

### Descripción

El sistema utilizará un modelo de multi-tenancy basado en `tenant_id`.

Cada registro perteneciente a una empresa debe estar asociado a su tenant.

```text
Tenant A
   │
   ├── Users
   ├── Displays
   ├── Advertisements
   └── Menus

Tenant B
   │
   ├── Users
   ├── Displays
   ├── Advertisements
   └── Menus
```

### Mecanismos de protección

```text
tenant_id
     ↓
Global Scope
     ↓
Policies
     ↓
Form Requests
     ↓
Datos aislados
```

### Criterios de aceptación

* [ ] Un usuario solo puede consultar información de su tenant.
* [ ] Un usuario no puede modificar información de otro tenant.
* [ ] Un usuario no puede eliminar información de otro tenant.
* [ ] Los Global Scopes filtran los registros correctamente.
* [ ] Las Policies validan el acceso.
* [ ] El SuperAdmin puede acceder globalmente.
* [ ] El SuperAdmin utiliza `tenant_id = NULL`.

### Sprint

**Sprint 2**

### Prioridad

**Crítica**

---

# E3 — Publicidad y Displays

## HU-07 — Gestión de publicidades

**Como cliente-administrador**, quiero crear, modificar y deshabilitar publicidades para administrar el contenido que mostraré en mis displays.

### Funcionalidades

* Crear publicidad.
* Consultar publicidad.
* Modificar publicidad.
* Deshabilitar publicidad.
* Gestionar tipo de contenido.
* Configurar duración.
* Configurar periodo de publicación.

### Tipos previstos

```text
image
video
```

### Criterios de aceptación

* [ ] Se puede crear una publicidad.
* [ ] Se puede modificar.
* [ ] Se puede deshabilitar.
* [ ] La publicidad pertenece a un tenant.
* [ ] Se valida el tipo de contenido.
* [ ] Se controla la duración.
* [ ] Solo usuarios autorizados pueden modificarla.

### Sprint

**Sprint 3 — Publicidad y Displays**

### Prioridad

**Alta**

---

## HU-08 — Gestión de Displays

**Como cliente-administrador**, quiero registrar y administrar mis displays para controlar los dispositivos donde mostraré mi contenido.

### Funcionalidades

* Registrar display.
* Consultar display.
* Modificar display.
* Activar/desactivar display.
* Identificar display mediante código.
* Asociar display a un tenant.

### Criterios de aceptación

* [ ] Se puede registrar un display.
* [ ] Cada display pertenece a un tenant.
* [ ] Se puede consultar su información.
* [ ] Se puede modificar.
* [ ] Se puede activar o desactivar.
* [ ] Un tenant no puede acceder a displays de otro tenant.

### Sprint

**Sprint 3**

### Prioridad

**Alta**

---

## HU-09 — Asociación de publicidades y displays

**Como cliente-administrador**, quiero asociar una publicidad a uno o varios displays para decidir dónde se mostrará cada anuncio.

### Relación

La asociación se realiza mediante:

```text
display_advertisements
```

Conceptualmente:

```text
Display
   │
   ├── Publicidad A
   ├── Publicidad B
   └── Publicidad C

Publicidad A
   │
   ├── Display 1
   └── Display 2
```

Esto permite una relación de muchos a muchos entre displays y publicidades.

### Criterios de aceptación

* [ ] Una publicidad puede asociarse a varios displays.
* [ ] Un display puede tener varias publicidades.
* [ ] La asociación pertenece al tenant correspondiente.
* [ ] No se pueden asociar recursos de diferentes tenants.
* [ ] Las asociaciones pueden consultarse.
* [ ] Las asociaciones pueden modificarse o eliminarse según permisos.

### Sprint

**Sprint 3**

### Prioridad

**Alta**

---

## HU-10 — Búsqueda de publicidades y displays

**Como cliente**, quiero buscar mis publicidades y displays por nombre o código para encontrar rápidamente la información que necesito.

### Criterios de aceptación

* [ ] Se puede buscar por nombre.
* [ ] Se puede buscar por código.
* [ ] Los resultados pertenecen únicamente al tenant del usuario.
* [ ] Se pueden combinar filtros.
* [ ] La búsqueda utiliza los identificadores internos correspondientes.

### Ejemplos

```text
Publicidad:
"Coca Cola Verano"

Display:
"Display Plaza Central"
```

Internamente los registros continúan identificándose mediante su `id`.

### Sprint

**Sprint 3**

### Prioridad

**Media**

---

# E4 — Menús y Programación

## HU-11 — Gestión de menús

**Como cliente-administrador**, quiero crear y administrar menús para definir el contenido interactivo de mis displays.

### Funcionalidades

* Crear menú.
* Modificar menú.
* Consultar menú.
* Activar/desactivar menú.
* Publicar menú.
* Asociar menú a un display.

### Criterios de aceptación

* [ ] Se puede crear un menú.
* [ ] El menú pertenece a un tenant.
* [ ] Se puede modificar.
* [ ] Se puede activar/desactivar.
* [ ] Se puede publicar.
* [ ] Solo usuarios autorizados pueden administrarlo.

### Sprint

**Sprint 4 — Menús y programación**

### Prioridad

**Alta**

---

## HU-12 — Organización de elementos del menú

**Como cliente-administrador**, quiero organizar los elementos de un menú para definir su estructura y orden de presentación.

### Funcionalidades

* Crear elementos.
* Modificar elementos.
* Eliminar/desactivar elementos.
* Cambiar orden.
* Crear estructuras jerárquicas.
* Crear subelementos.

### Estructura

```text
Menu
 │
 ├── Item 1
 ├── Item 2
 │    ├── SubItem 1
 │    └── SubItem 2
 └── Item 3
```

### Tecnología prevista

```text
@dnd-kit/core
```

para implementar posteriormente la funcionalidad de Drag & Drop.

### Criterios de aceptación

* [ ] Se pueden crear elementos.
* [ ] Se puede modificar su información.
* [ ] Se puede modificar su orden.
* [ ] Se pueden crear elementos hijos.
* [ ] La estructura pertenece al tenant correspondiente.
* [ ] El orden almacenado se conserva.

### Sprint

**Sprint 4**

### Prioridad

**Media-Alta**

---

## HU-13 — Programación de publicidades

**Como cliente-administrador**, quiero programar la fecha y horario de mis publicidades para controlar cuándo serán mostradas.

### Funcionalidades

* Definir día de la semana.
* Definir hora de inicio.
* Definir hora de finalización.
* Definir fecha inicial.
* Definir fecha final.
* Asociar programación a una publicidad asignada a un display.

### Modelo

```text
Display
   ↓
DisplayAdvertisement
   ↓
AdSchedule
```

### Criterios de aceptación

* [ ] Se puede definir un día de reproducción.
* [ ] Se puede definir una hora inicial.
* [ ] Se puede definir una hora final.
* [ ] La hora final debe ser posterior a la inicial.
* [ ] Se puede establecer una fecha de inicio.
* [ ] Se puede establecer una fecha de finalización.
* [ ] Las fechas deben ser válidas.
* [ ] Un usuario no puede programar publicidad de otro tenant.
* [ ] El SuperAdmin puede administrar programaciones globalmente.

### Sprint

**Sprint 4**

### Prioridad

**Alta**

---

# E5 — Analítica y Experiencia

## HU-14 — Estadísticas del cliente

**Como cliente-administrador**, quiero consultar estadísticas de mis publicidades y displays para conocer su rendimiento.

### Indicadores previstos

```text
Publicidad más visualizada
Display más utilizado
Cantidad de interacciones
Tiempo de reproducción
Cantidad de eventos
```

### Criterios de aceptación

* [ ] El cliente puede consultar estadísticas.
* [ ] Solo puede consultar estadísticas de su tenant.
* [ ] Se pueden obtener estadísticas de publicidades.
* [ ] Se pueden obtener estadísticas de displays.
* [ ] Se pueden contabilizar eventos.
* [ ] La información puede utilizarse para tomar decisiones.

### Sprint

**Sprint 5 — Analítica y PWA**

### Prioridad

**Media-Alta**

---

## HU-15 — Estadísticas globales del SuperAdmin

**Como superadministrador**, quiero consultar estadísticas generales de la plataforma para conocer el uso de los servicios por parte de mis clientes.

### Indicadores previstos

```text
Clientes registrados
Displays activos
Publicidades creadas
Usuarios registrados
Eventos generados
Tenants activos
```

### Diferencia con HU-14

La analítica debe respetar dos niveles:

```text
CLIENTE
   ↓
Solo estadísticas de SU tenant


SUPERADMIN
   ↓
Estadísticas de TODA la plataforma
```

### Criterios de aceptación

* [ ] El SuperAdmin puede consultar estadísticas globales.
* [ ] Puede consultar cantidad de tenants.
* [ ] Puede consultar usuarios registrados.
* [ ] Puede consultar displays.
* [ ] Puede consultar publicidades.
* [ ] Puede consultar eventos.
* [ ] Un tenant normal no puede acceder a estas estadísticas globales.

### Sprint

**Sprint 5**

### Prioridad

**Media**

---

## HU-16 — Funcionamiento offline del display

**Como cliente**, quiero que el contenido del display pueda funcionar parcialmente sin conexión para mantener disponible el servicio ante problemas de conectividad.

### Objetivo

El display debe poder mantener determinados contenidos disponibles localmente cuando temporalmente no exista conexión con el servidor.

### Arquitectura prevista

```text
Laravel
    ↓
API / Datos
    ↓
React
    ↓
PWA
    ↓
Service Worker / Workbox
    ↓
Cache Storage / IndexedDB
    ↓
Display
```

### Tecnologías previstas

* React.
* PWA.
* Service Worker.
* Workbox.
* Cache Storage.
* IndexedDB.

### Criterios de aceptación

* [ ] El display puede instalarse como PWA.
* [ ] Los recursos esenciales pueden almacenarse localmente.
* [ ] El contenido previamente descargado puede visualizarse sin conexión.
* [ ] El sistema detecta nuevamente la conexión.
* [ ] El contenido puede sincronizarse cuando vuelve la conectividad.
* [ ] La pérdida temporal de conexión no debe provocar el fallo completo del display.

### Sprint

**Sprint 5**

### Prioridad

**Media**

---

# 6. Requisitos Funcionales

Los principales requisitos funcionales del sistema son:

| Código | Requisito                                                                     |
| ------ | ----------------------------------------------------------------------------- |
| RF-01  | El sistema debe permitir autenticación de usuarios.                           |
| RF-02  | El sistema debe gestionar roles y permisos.                                   |
| RF-03  | El sistema debe permitir administrar tenants.                                 |
| RF-04  | El sistema debe permitir administrar usuarios.                                |
| RF-05  | El sistema debe aislar los datos entre tenants.                               |
| RF-06  | El sistema debe permitir administrar displays.                                |
| RF-07  | El sistema debe permitir administrar publicidades.                            |
| RF-08  | El sistema debe permitir asociar publicidades a displays.                     |
| RF-09  | El sistema debe permitir buscar displays y publicidades.                      |
| RF-10  | El sistema debe permitir administrar menús.                                   |
| RF-11  | El sistema debe permitir administrar elementos de menú.                       |
| RF-12  | El sistema debe permitir programar publicidades.                              |
| RF-13  | El sistema debe registrar eventos de analítica.                               |
| RF-14  | El sistema debe proporcionar estadísticas por tenant.                         |
| RF-15  | El sistema debe proporcionar estadísticas globales al SuperAdmin.             |
| RF-16  | El sistema debe permitir funcionamiento parcialmente offline en los displays. |

---

# 7. Requisitos No Funcionales

## RNF-01 — Seguridad

El sistema debe proteger la información de cada cliente mediante autenticación, autorización, Policies y aislamiento multi-tenant.

## RNF-02 — Aislamiento de datos

Un tenant no debe poder consultar ni modificar información perteneciente a otro tenant.

## RNF-03 — Escalabilidad

La arquitectura debe permitir agregar nuevos tenants sin crear una instalación independiente del sistema para cada cliente.

## RNF-04 — Mantenibilidad

El código debe organizarse siguiendo las convenciones de Laravel, React e Inertia.js, manteniendo responsabilidades separadas.

## RNF-05 — Integridad

La base de datos debe utilizar claves foráneas y restricciones apropiadas para evitar registros inconsistentes.

## RNF-06 — Usabilidad

Las interfaces administrativas deben permitir realizar las operaciones principales de forma clara y sencilla.

## RNF-07 — Disponibilidad

Los displays deben poder mantener contenido previamente sincronizado durante interrupciones temporales de conectividad.

## RNF-08 — Rendimiento

Las consultas principales deben utilizar filtros, relaciones y paginación cuando sea necesario para evitar cargar cantidades excesivas de información.

---

# 8. Matriz General de Historias de Usuario

| HU    | Épica | Historia                      | Prioridad  | Sprint   | Estado     |
| ----- | ----- | ----------------------------- | ---------- | -------- | ---------- |
| HU-01 | E1    | Configuración de arquitectura | Alta       | Sprint 1 | Completada |
| HU-02 | E1    | Modelo de datos               | Alta       | Sprint 1 | Completada |
| HU-03 | E2    | Autenticación                 | Alta       | Sprint 2 | Completada |
| HU-04 | E2    | Gestión de usuarios           | Alta       | Sprint 2 | Completada |
| HU-05 | E2    | Roles y permisos              | Alta       | Sprint 2 | Completada |
| HU-06 | E2    | Multi-tenancy                 | Crítica    | Sprint 2 | Completada |
| HU-07 | E3    | Gestión de publicidades       | Alta       | Sprint 3 | Pendiente  |
| HU-08 | E3    | Gestión de displays           | Alta       | Sprint 3 | Pendiente  |
| HU-09 | E3    | Asociación publicidad-display | Alta       | Sprint 3 | Pendiente  |
| HU-10 | E3    | Búsqueda                      | Media      | Sprint 3 | Pendiente  |
| HU-11 | E4    | Gestión de menús              | Alta       | Sprint 4 | Pendiente  |
| HU-12 | E4    | Organización de menú          | Media-Alta | Sprint 4 | Pendiente  |
| HU-13 | E4    | Programación                  | Alta       | Sprint 4 | Pendiente  |
| HU-14 | E5    | Estadísticas del cliente      | Media-Alta | Sprint 5 | Pendiente  |
| HU-15 | E5    | Estadísticas globales         | Media      | Sprint 5 | Pendiente  |
| HU-16 | E5    | Funcionamiento offline        | Media      | Sprint 5 | Pendiente  |

> **Nota:** Los estados deben actualizarse conforme avance el desarrollo. Las HU-01 a HU-06 corresponden a la base de arquitectura, autenticación, seguridad y multi-tenancy desarrollada inicialmente.

---

# 9. Distribución de Historias por Sprint

## Sprint 1 — Base de la plataforma

### Meta

Construir las bases técnicas y estructurales del sistema.

### Historias

* HU-01
* HU-02

### Componentes principales

* Laravel 12.
* React.
* Inertia.js.
* MySQL.
* Migraciones.
* Modelos.
* Relaciones Eloquent.
* Estructura inicial del proyecto.

---

## Sprint 2 — Autenticación y Seguridad

### Meta

Construir un sistema seguro de autenticación, autorización y aislamiento de información.

### Historias

* HU-03
* HU-04
* HU-05
* HU-06

### Componentes principales

* Login.
* Logout.
* Registro.
* Laravel Breeze.
* Spatie Permission.
* Roles.
* Permisos.
* Policies.
* Form Requests.
* Multi-tenancy.
* Global Scopes.
* Gestión de usuarios.

### Estado

**Completado / validado mediante pruebas iniciales.**

---

## Sprint 3 — Publicidad y Displays

### Meta

Permitir al cliente administrar los dispositivos y contenidos publicitarios.

### Historias

* HU-07
* HU-08
* HU-09
* HU-10

### Componentes principales

* CRUD de displays.
* CRUD de publicidades.
* Asociaciones.
* Búsqueda.
* Validaciones.
* Estados.
* Interfaces React/Inertia.

---

## Sprint 4 — Menús y Programación

### Meta

Permitir crear experiencias interactivas y controlar cuándo se reproducen las publicidades.

### Historias

* HU-11
* HU-12
* HU-13

### Componentes principales

* Menús.
* Menu Items.
* Relaciones jerárquicas.
* Ordenamiento.
* Drag & Drop.
* Programación de publicidades.
* Horarios.

---

## Sprint 5 — Analítica y PWA

### Meta

Medir el uso de la plataforma y garantizar una experiencia resiliente en los displays.

### Historias

* HU-14
* HU-15
* HU-16

### Componentes principales

* Analytics.
* Dashboard del cliente.
* Dashboard del SuperAdmin.
* Estadísticas.
* PWA.
* Service Worker.
* Workbox.
* Cache Storage.
* IndexedDB.
* Sincronización offline.

---

# 10. Dependencias entre Historias

Las historias presentan las siguientes dependencias:

```text
HU-01
  ↓
HU-02
  ↓
HU-03
  ↓
HU-05
  ↓
HU-06
  ↓
HU-04
  ↓
HU-07 ─────┐
HU-08 ─────┼──→ HU-09
           │
           └──→ HU-10
                  ↓
                HU-11
                  ↓
                HU-12
                  ↓
                HU-13
                  ↓
           ┌──────┴──────┐
         HU-14         HU-15
           │              │
           └──────┬───────┘
                  ↓
                HU-16
```

La seguridad y el aislamiento multi-tenant deben estar implementados antes de construir las funcionalidades principales de administración de información.

---

# 11. Reglas de Negocio Principales

## RN-01 — Tenant del usuario

Todo usuario normal debe pertenecer a un tenant.

```text
tenant_admin → tenant_id obligatorio
user         → tenant_id obligatorio
superadmin   → tenant_id = NULL
```

## RN-02 — Administrador por tenant

Solo puede existir un `tenant_admin` por tenant.

```text
Tenant Coca-Cola
    └── 1 tenant_admin
```

## RN-03 — Aislamiento

Un usuario perteneciente al Tenant A no puede acceder a información del Tenant B.

## RN-04 — SuperAdmin

El SuperAdmin tiene acceso global a la plataforma.

## RN-05 — Eliminación de usuarios

Los usuarios no se eliminan físicamente durante la desactivación.

Se utiliza:

```text
status = inactive
```

## RN-06 — Roles

Los usuarios pueden tener los roles:

```text
superadmin
tenant_admin
user
```

## RN-07 — Publicidades

Una publicidad puede asociarse a múltiples displays.

## RN-08 — Displays

Un display puede tener múltiples publicidades asociadas.

## RN-09 — Programación

Una programación pertenece a una asociación entre display y publicidad.

## RN-10 — Analítica

Los clientes solamente pueden consultar estadísticas correspondientes a su propio tenant.

El SuperAdmin puede consultar estadísticas globales.

---

# 12. Matriz Actor vs Funcionalidad

| Funcionalidad              | SuperAdmin | Tenant Admin |      User      |
| -------------------------- | :--------: | :----------: | :------------: |
| Administrar tenants        |      ✅     |       ❌      |        ❌       |
| Administrar usuarios       |      ✅     |       ✅      | Según permisos |
| Gestionar roles            |      ✅     |   Limitado   |        ❌       |
| Ver displays               |      ✅     |       ✅      |        ✅       |
| Crear displays             |      ✅     |       ✅      | Según permisos |
| Gestionar publicidades     |      ✅     |       ✅      | Según permisos |
| Asociar publicidad-display |      ✅     |       ✅      | Según permisos |
| Gestionar menús            |      ✅     |       ✅      | Según permisos |
| Programar publicidad       |      ✅     |       ✅      | Según permisos |
| Ver estadísticas de tenant |      ✅     |       ✅      | Según permisos |
| Ver estadísticas globales  |      ✅     |       ❌      |        ❌       |
| Administrar otros tenants  |      ✅     |       ❌      |        ❌       |

---

# 13. Criterios Generales de Aceptación

El MVP podrá considerarse funcional cuando:

* [ ] Los usuarios puedan autenticarse correctamente.
* [ ] Los roles y permisos funcionen.
* [ ] El SuperAdmin tenga acceso global.
* [ ] Los usuarios de cada cliente estén aislados por tenant.
* [ ] Los clientes puedan administrar sus recursos.
* [ ] Las Policies impidan accesos no autorizados.
* [ ] Las validaciones impidan datos inconsistentes.
* [ ] Los displays puedan asociarse con publicidades.
* [ ] Las publicidades puedan programarse.
* [ ] Los menús puedan administrarse.
* [ ] Las estadísticas respeten el aislamiento de tenants.
* [ ] El display pueda mantener contenido previamente sincronizado cuando exista una pérdida temporal de conexión.

---

# 14. Trazabilidad Inicial

La trazabilidad completa de cada Historia de Usuario se documentará posteriormente en archivos individuales.

Ejemplo:

```text
docs/
│
├── 01. Contexto y diagnóstico inicial.md
├── 02. MVP y propuesta de valor.md
├── 03. Requisitos e Historias de Usuario.md
│
├── Historias de Usuario/
│   ├── HU-01.md
│   ├── HU-02.md
│   ├── HU-03.md
│   ├── ...
│   └── HU-16.md
│
└── 07. Pivot & Plan / Ready to Sprint.md
```

Cada historia individual podrá documentar:

```text
Historia de Usuario
        ↓
Criterios de aceptación
        ↓
Implementación
        ↓
Archivos modificados
        ↓
Modelos
        ↓
Controllers
        ↓
Requests
        ↓
Policies
        ↓
Migraciones
        ↓
Frontend
        ↓
Pruebas
        ↓
Resultado
```

Esto permitirá mantener una relación clara entre los **requisitos del proyecto y el código realmente desarrollado**.

---

# 15. Estado Actual del Proyecto

Actualmente se encuentra implementada y validada la base correspondiente a los primeros dos sprints:

```text
Sprint 1 — Base de la plataforma
        ✅

Sprint 2 — Autenticación y seguridad
        ✅

Sprint 3 — Publicidad y Displays
        ⏳

Sprint 4 — Menús y programación
        ⏳

Sprint 5 — Analítica y PWA
        ⏳
```

El siguiente ciclo de desarrollo corresponde a **Sprint 3 — Publicidad y Displays**, donde se comenzará con la implementación de las funcionalidades principales del negocio.

---

# 16. Resumen del MVP

El MVP busca proporcionar una plataforma SaaS capaz de permitir que múltiples empresas administren sus propios contenidos publicitarios y displays desde un único sistema.

El flujo principal será:

```text
Empresa cliente
      ↓
Cuenta Tenant Admin
      ↓
Usuarios
      ↓
Displays
      ↓
Publicidades
      ↓
Asociación Display ↔ Publicidad
      ↓
Programación
      ↓
Reproducción en Display
      ↓
Interacciones
      ↓
Analítica
```

La plataforma mantendrá el aislamiento entre empresas mediante el modelo multi-tenant y proporcionará al propietario del SaaS una visión global mediante el rol `superadmin`.

**Objetivo final del MVP:**

> Proporcionar una plataforma centralizada, segura y multi-tenant para administrar displays publicitarios, contenido, programación y analítica, permitiendo que cada empresa gestione exclusivamente sus propios recursos mientras el propietario de la plataforma mantiene control global del sistema.
