# 07. Pivot & Plan / Ready to Sprint

## 1. Decisiones Consolidadas de Arquitectura y Stack

## 1.1 Arquitectura general

El proyecto utiliza una arquitectura web cliente-servidor con separación entre la interfaz de usuario y el backend.

```text
                         ┌──────────────────────┐
                         │      USUARIO         │
                         └──────────┬───────────┘
                                    │
                                    ▼
                         ┌──────────────────────┐
                         │   FRONTEND WEB       │
                         │ React + TypeScript   │
                         │       + Inertia      │
                         └──────────┬───────────┘
                                    │
                           HTTP / Inertia
                                    │
                                    ▼
                         ┌──────────────────────┐
                         │       BACKEND        │
                         │      Laravel 12      │
                         │                      │
                         │ Controllers          │
                         │ Form Requests        │
                         │ Policies             │
                         │ Models / Eloquent    │
                         │ Multi-tenancy        │
                         └──────────┬───────────┘
                                    │
                                    ▼
                         ┌──────────────────────┐
                         │        MySQL         │
                         │      Base de datos   │
                         └──────────────────────┘
```

La aplicación está diseñada para funcionar como un sistema SaaS multi-tenant. Diferentes empresas pueden utilizar la misma plataforma manteniendo sus datos aislados mediante `tenant_id`, Global Scopes, relaciones y Policies.

---

## 1.2 Stack tecnológico

| Tecnología                    | Uso                           | Justificación                                                                                                             |
| ----------------------------- | ----------------------------- | ------------------------------------------------------------------------------------------------------------------------- |
| **PHP 8.3**                   | Lenguaje backend              | Proporciona compatibilidad con Laravel 12 y un entorno moderno para desarrollo web.                                       |
| **Laravel 12**                | Framework backend             | Proporciona estructura MVC, routing, autenticación, validación, ORM, migraciones y herramientas de seguridad.             |
| **MySQL**                     | Base de datos                 | Base de datos relacional adecuada para manejar las relaciones entre empresas, usuarios, displays, publicidad y contenido. |
| **Eloquent ORM**              | Acceso a datos                | Permite trabajar con modelos y relaciones de forma integrada con Laravel.                                                 |
| **React**                     | Interfaz de usuario           | Permite construir interfaces dinámicas y reutilizables.                                                                   |
| **TypeScript**                | Tipado del frontend           | Reduce errores mediante tipado estático y mejora la mantenibilidad del código.                                            |
| **Inertia.js**                | Comunicación Frontend/Backend | Permite utilizar React con Laravel sin requerir inicialmente una API REST completamente separada.                         |
| **Tailwind CSS**              | Estilos                       | Permite construir interfaces rápidamente mediante clases utilitarias.                                                     |
| **Spatie Laravel Permission** | Roles y permisos              | Proporciona una implementación estructurada de roles y permisos integrada con Laravel.                                    |
| **Vite**                      | Build frontend                | Permite compilar y servir los recursos de React y TypeScript durante el desarrollo.                                       |
| **Git**                       | Control de versiones          | Permite mantener historial de cambios y facilitar la colaboración.                                                        |
| **GitHub**                    | Repositorio remoto            | Centraliza el código fuente y facilita el control y seguimiento del proyecto.                                             |

---

# 2. Decisiones de Arquitectura

## 2.1 Modelo multi-tenant

Se decidió utilizar **multi-tenancy basado en filas (row-level tenancy)**.

Cada recurso que pertenece a una empresa utiliza un `tenant_id` para identificar su propietario.

```text
TENANT
  │
  ├── USERS
  ├── DISPLAYS
  ├── ADVERTISEMENTS
  ├── MENUS
  └── ANALYTICS EVENTS
```

El aislamiento se complementa mediante:

* Global Scopes.
* Policies.
* Form Requests.
* Relaciones Eloquent.
* Validaciones de pertenencia al tenant.

No se utilizará una base de datos independiente por cliente ni el paquete `stancl/tenancy`.

---

## 2.2 Roles

El sistema utiliza tres roles principales:

```text
SUPERADMIN
    │
    └── Control global de la plataforma

TENANT_ADMIN
    │
    └── Administración de una empresa

USER
    │
    └── Usuario operativo de una empresa
```

### SuperAdmin

Pertenece al sistema global y tiene `tenant_id = NULL`.

Puede administrar todos los tenants y recursos.

### Tenant Admin

Pertenece a un tenant específico y administra los recursos de su empresa.

Cada tenant puede tener como máximo un `tenant_admin`.

### User

Pertenece a un tenant y dispone únicamente de los permisos asignados.

---

# 3. Validación de Madurez — Ready to Sprint

La siguiente matriz permite determinar si las condiciones necesarias para comenzar o continuar el desarrollo están disponibles.

| Prerrequisito                    | Estado           | Evidencia                                |
| -------------------------------- | ---------------- | ---------------------------------------- |
| Problema definido                | ✅ Listo          | Documento de contexto y diagnóstico.     |
| Propuesta de valor definida      | ✅ Listo          | Documento MVP.                           |
| Alcance del MVP definido         | ✅ Listo          | Módulos y funcionalidades documentadas.  |
| Roles definidos                  | ✅ Listo          | SuperAdmin, Tenant Admin y User.         |
| Arquitectura definida            | ✅ Listo          | Laravel + React + Inertia + MySQL.       |
| Estrategia multi-tenant definida | ✅ Listo          | `tenant_id` + Global Scopes + Policies.  |
| Base de datos diseñada           | ✅ Listo          | Migraciones Laravel.                     |
| Modelos principales creados      | ✅ Listo          | Modelos Eloquent.                        |
| Autenticación implementada       | ✅ Listo          | Laravel Breeze/Inertia.                  |
| Roles y permisos implementados   | ✅ Listo          | Spatie Laravel Permission.               |
| Policies implementadas           | ✅ Listo          | Policies por recurso.                    |
| Validaciones implementadas       | ✅ Listo          | Form Requests.                           |
| Interfaz inicial                 | 🟡 En desarrollo | Componentes React/Inertia.               |
| Pruebas funcionales completas    | 🟡 Pendiente     | Se deben ejecutar y documentar.          |
| Despliegue                       | 🟡 Pendiente     | Infraestructura de producción pendiente. |
| Documentación técnica            | 🟡 En desarrollo | Se está consolidando.                    |

### Estado general

**CONDICIONADO**

Las bases técnicas y arquitectónicas están listas para continuar el desarrollo. Sin embargo, antes de considerar el proyecto completamente preparado para una entrega final se deben completar las pruebas funcionales, interfaz, documentación y despliegue.

---

# 4. Plan de Entregas por Sprints

## Sprint 0 — Fundaciones

### Meta

Establecer la infraestructura y arquitectura base del proyecto.

### Actividades

* Crear repositorio Git.
* Configurar Laravel 12.
* Configurar React + TypeScript.
* Configurar Inertia.
* Configurar Tailwind CSS.
* Configurar MySQL.
* Configurar variables de entorno.
* Configurar migraciones.
* Configurar autenticación.
* Configurar Spatie Permission.
* Definir estrategia multi-tenant.

### Resultado

Proyecto ejecutándose correctamente con la arquitectura base configurada.

---

# Sprint 1 — Identidad y Multi-tenancy

### Meta

Implementar la estructura de usuarios, empresas y control de acceso.

### Historias relacionadas

* Registro de empresa.
* Inicio de sesión.
* Gestión de usuarios.
* Gestión de roles.
* Gestión de permisos.
* Administración del SuperAdmin.
* Aislamiento entre tenants.

### Resultado

Cada empresa puede disponer de usuarios y acceder únicamente a sus propios recursos.

---

# Sprint 2 — Gestión de Displays

### Meta

Permitir administrar los dispositivos pertenecientes a cada empresa.

### Funcionalidades

* Crear display.
* Listar displays.
* Consultar display.
* Editar display.
* Activar/desactivar display.
* Validar pertenencia al tenant.

### Resultado

Cada empresa puede administrar sus propios dispositivos.

---

# Sprint 3 — Gestión de Publicidad

### Meta

Implementar la administración del contenido publicitario.

### Funcionalidades

* Crear publicidad.
* Editar publicidad.
* Consultar publicidad.
* Activar/desactivar publicidad.
* Definir duración.
* Definir tipo de contenido.
* Asociar publicidad con displays.

### Resultado

Los administradores pueden controlar qué publicidad pertenece a cada display.

---

# Sprint 4 — Programación y Menús

### Meta

Controlar cuándo se muestran los anuncios y administrar contenido interactivo.

### Funcionalidades

* Crear horarios.
* Editar horarios.
* Definir días.
* Definir horas.
* Definir periodos.
* Crear menús.
* Crear elementos de menú.
* Organizar elementos.
* Gestionar contenido interactivo.

### Resultado

Los contenidos pueden organizarse y programarse según las necesidades de cada empresa.

---

# Sprint 5 — Analítica y Validación

### Meta

Obtener información sobre el funcionamiento del sistema y validar el MVP.

### Funcionalidades

* Registro de eventos.
* Visualización de métricas.
* Métricas por display.
* Métricas por publicidad.
* Métricas por menú.
* Pruebas funcionales.
* Pruebas de aislamiento entre tenants.

### Resultado

MVP funcional validado mediante escenarios de prueba.

---

# Sprint 6 — Preparación de Entrega

### Meta

Preparar el sistema para demostración y eventual despliegue.

### Actividades

* Corrección de errores.
* Pruebas finales.
* Optimización.
* Revisión de seguridad.
* Documentación.
* Configuración de producción.
* Preparación del repositorio.
* Preparación de demostración.

### Resultado

Versión candidata para entrega del MVP.

---

# 5. Matriz de Trazabilidad

| Problema detectado                       | Capacidad del MVP                | Historia de Usuario                  | Tablas principales                             | Evidencia de aceptación                                   |
| ---------------------------------------- | -------------------------------- | ------------------------------------ | ---------------------------------------------- | --------------------------------------------------------- |
| Gestión dispersa de usuarios             | Gestión centralizada de usuarios | HU-01 Gestionar usuarios             | `users`, `roles`, `permissions`                | Usuario creado y asignado correctamente.                  |
| Falta de separación entre empresas       | Multi-tenancy                    | HU-02 Aislar información por empresa | `tenants`, `users`                             | Usuario de Tenant A no puede acceder a datos de Tenant B. |
| Dificultad para administrar dispositivos | Gestión de displays              | HU-03 Gestionar displays             | `displays`                                     | Display creado y asociado al tenant correcto.             |
| Gestión manual de publicidad             | Gestión de anuncios              | HU-04 Gestionar publicidad           | `advertisements`                               | Anuncio creado y administrado correctamente.              |
| Falta de planificación                   | Programación de publicidad       | HU-05 Programar publicidad           | `ad_schedules`, `display_advertisements`       | Horario creado y validado.                                |
| Contenido poco organizado                | Gestión de menús                 | HU-06 Gestionar menús                | `menus`, `menu_items`                          | Menú y elementos creados correctamente.                   |
| Falta de información de utilización      | Analítica                        | HU-07 Consultar métricas             | `analytics_events`                             | Eventos registrados y consultables.                       |
| Acceso no controlado                     | Roles y permisos                 | HU-08 Controlar acceso               | `users`, `roles`, `permissions`, tablas Spatie | Operaciones no autorizadas son rechazadas.                |

---

# 6. Gestión de Riesgos Técnicos

| Riesgo                                  | Impacto  | Probabilidad | Mitigación                                                                 |
| --------------------------------------- | -------- | ------------ | -------------------------------------------------------------------------- |
| Filtración de información entre tenants | 🔴 Alto  | Media        | Global Scopes + Policies + validaciones en Form Requests.                  |
| Usuario obtiene acceso no autorizado    | 🔴 Alto  | Media        | Spatie Permission + Policies + middleware/autorización.                    |
| Crecimiento de eventos de analítica     | 🟠 Medio | Media        | Indexación, paginación y optimización de consultas.                        |
| Pérdida de conectividad del display     | 🟠 Medio | Media        | Diseñar mecanismos de almacenamiento local/offline para futuras versiones. |
| Errores en programación de anuncios     | 🟠 Medio | Media        | Validación de fechas, horas y relaciones.                                  |
| Consultas N+1                           | 🟡 Medio | Media        | Uso de `with()` y carga anticipada de relaciones.                          |
| Crecimiento del volumen de publicidad   | 🟡 Medio | Media        | Estrategia de almacenamiento y gestión de archivos.                        |
| Dependencia de servicios externos       | 🟡 Medio | Baja         | Mantener integraciones desacopladas del núcleo del sistema.                |
| Errores durante despliegue              | 🟠 Medio | Media        | Variables de entorno, migraciones y proceso de despliegue documentado.     |

---

# 7. Indicadores de Seguimiento del Desarrollo

Los siguientes indicadores permitirán medir el progreso y la calidad del proyecto durante el desarrollo.

| Indicador                     | Qué mide               | Objetivo                                                  |
| ----------------------------- | ---------------------- | --------------------------------------------------------- |
| Historias completadas         | Progreso funcional     | Incrementar progresivamente hasta completar el MVP.       |
| Historias pendientes          | Trabajo restante       | Mantenerlas controladas por Sprint.                       |
| Bugs encontrados              | Calidad del desarrollo | Reducir progresivamente antes de la entrega.              |
| Bugs críticos abiertos        | Riesgo funcional       | Llegar a 0 antes de la entrega.                           |
| Pruebas aprobadas             | Estabilidad            | Alcanzar una alta tasa de pruebas exitosas.               |
| Errores de autorización       | Seguridad              | Llegar a 0 accesos no autorizados.                        |
| Errores de aislamiento tenant | Seguridad              | 0 casos permitidos.                                       |
| Tiempo de respuesta           | Rendimiento            | Mantener tiempos adecuados para operaciones normales.     |
| Cobertura de pruebas          | Calidad                | Incrementar conforme se incorporen pruebas automatizadas. |
| Errores de integración        | Estabilidad            | Reducir antes de cada entrega.                            |

---

# 8. Definition of Done

Una funcionalidad podrá considerarse terminada cuando:

* [ ] La historia de usuario está implementada.
* [ ] La validación de datos está implementada.
* [ ] La autorización está implementada.
* [ ] La funcionalidad respeta el aislamiento entre tenants.
* [ ] Las relaciones de base de datos funcionan correctamente.
* [ ] La interfaz permite ejecutar el flujo.
* [ ] No existen errores críticos conocidos.
* [ ] Se realizó una prueba funcional.
* [ ] La documentación correspondiente fue actualizada.
* [ ] El código fue registrado mediante Git.

---

# 9. Estado actual del proyecto

El proyecto se encuentra en una etapa de **desarrollo avanzado del backend y consolidación de la arquitectura**.

Actualmente se cuenta con:

* Laravel 12 configurado.
* MySQL configurado.
* Autenticación.
* Roles y permisos.
* SuperAdmin.
* Multi-tenancy.
* Policies.
* Form Requests.
* Migraciones.
* Modelos Eloquent.
* Controladores principales.
* Gestión de usuarios.
* Gestión de publicidad.
* Displays.
* Menús.
* Programación de publicidad.
* Analítica.
* Seeders iniciales.

La siguiente etapa se concentra en:

1. Completar las interfaces del frontend.
2. Crear y documentar las historias de usuario.
3. Ejecutar pruebas funcionales completas.
4. Corregir errores encontrados.
5. Completar la documentación técnica.
6. Preparar el repositorio GitHub.
7. Preparar el despliegue del MVP.

---

# 10. Criterio para iniciar un Sprint

Un Sprint podrá comenzar cuando:

* La meta del Sprint esté definida.
* Las historias involucradas estén identificadas.
* Los criterios de aceptación estén definidos.
* Las dependencias técnicas sean conocidas.
* Las tablas necesarias estén disponibles o planificadas.
* Los responsables de cada actividad estén definidos.
* No exista un bloqueo técnico crítico.

El objetivo de esta condición es evitar comenzar el desarrollo de funcionalidades sin conocer previamente su propósito, alcance y criterios de aceptación.
