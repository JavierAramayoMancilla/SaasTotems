# 01. Contexto y diagnóstico inicial

## 1. Ficha técnica del proyecto

| Campo                            | Información                                                                               |
| -------------------------------- | ----------------------------------------------------------------------------------------- |
| **Nombre del proyecto**          | Plataforma SaaS para gestión de Tótems Publicitarios                                      |
| **Tipo de proyecto**             | Proyecto académico con enfoque de producto SaaS                                           |
| **Dominio**                      | Gestión de publicidad digital e interactiva                                               |
| **Proceso central**              | Administración, programación y reproducción de contenido publicitario en tótems digitales |
| **Backend**                      | Laravel 12                                                                                |
| **Frontend**                     | React + TypeScript + Inertia.js                                                           |
| **Base de datos**                | MySQL                                                                                     |
| **Autenticación**                | Laravel Breeze / autenticación basada en sesiones                                         |
| **Autorización**                 | Spatie Laravel Permission + Policies                                                      |
| **Arquitectura multi-tenant**    | Row-Level Tenancy mediante `tenant_id` y Global Scopes                                    |
| **Comunicación en tiempo real**  | Laravel Reverb / WebSockets                                                               |
| **Aplicación para dispositivos** | PWA                                                                                       |
| **Gestión multimedia**           | Almacenamiento de imágenes y videos publicitarios                                         |
| **Repositorio Backend**          | Pendiente de registrar                                                                    |
| **Repositorio Frontend**         | Pendiente de registrar                                                                    |
| **Integrantes**                  | Pendiente de registrar                                                                    |
| **Estado actual**                | Desarrollo — backend y estructura principal implementados                                 |

### Objetivo general

Desarrollar una plataforma SaaS que permita administrar de forma centralizada diferentes dispositivos de publicidad digital, denominados tótems, proporcionando a cada cliente un entorno independiente para gestionar sus usuarios, displays, publicidades, menús, programación y métricas.

El sistema busca transformar la administración tradicional de contenido publicitario en un proceso centralizado, controlado y automatizado.

---

# 2. Contexto del problema

La publicidad mediante pantallas digitales requiere administrar diferentes elementos de manera coordinada: dispositivos físicos, contenido multimedia, horarios de reproducción, usuarios responsables de la administración y, posteriormente, información relacionada con el rendimiento de las publicaciones.

Cuando estos elementos se gestionan de forma independiente, pueden aparecer problemas como:

* Dificultad para controlar múltiples dispositivos.
* Actualización manual del contenido.
* Falta de una administración centralizada.
* Dificultad para determinar qué publicidad está asociada a cada dispositivo.
* Problemas para controlar horarios de reproducción.
* Falta de separación entre los datos de diferentes clientes.
* Poca visibilidad sobre las interacciones y reproducciones generadas.
* Dependencia de procesos manuales para administrar el contenido.

### Necesidad identificada

Se necesita una plataforma que permita centralizar la administración de los tótems y su contenido desde una aplicación web, manteniendo separados los datos de cada empresa cliente.

El sistema se plantea como un **SaaS multi-tenant**, donde una misma plataforma puede atender a múltiples empresas, pero cada empresa trabaja dentro de su propio entorno lógico y solamente puede acceder a la información que le corresponde.

---

# 3. Visión general de la solución

La plataforma estará compuesta por tres actores principales dentro de su arquitectura:

```text
                    ┌─────────────────────┐
                    │     SUPERADMIN      │
                    │ Dueño de la         │
                    │ plataforma SaaS     │
                    └──────────┬──────────┘
                               │
                               ▼
                    ┌─────────────────────┐
                    │    LARAVEL 12       │
                    │ Backend + API       │
                    │ Autenticación       │
                    │ Autorización        │
                    │ Multi-tenancy       │
                    └──────────┬──────────┘
                               │
              ┌────────────────┼────────────────┐
              │                │                │
              ▼                ▼                ▼
        ┌───────────┐    ┌───────────┐    ┌───────────┐
        │ Tenant A  │    │ Tenant B  │    │ Tenant C  │
        │ Empresa   │    │ Empresa   │    │ Empresa   │
        └─────┬─────┘    └─────┬─────┘    └─────┬─────┘
              │                │                │
              ▼                ▼                ▼
          Usuarios          Usuarios         Usuarios
          Displays          Displays         Displays
          Publicidad        Publicidad       Publicidad
          Menús             Menús            Menús
```

El aislamiento entre empresas se realiza mediante el concepto de **tenant**.

Cada usuario perteneciente a una empresa posee un `tenant_id`, mientras que el usuario `superadmin` pertenece al nivel global de la plataforma y no pertenece a un tenant específico.

---

# 4. Proceso central

El ciclo de vida principal del sistema puede representarse de la siguiente manera:

```text
                    INICIO
                      │
                      ▼
             Cliente adquiere servicio
                      │
                      ▼
             Se crea su Tenant
                      │
                      ▼
          Se crea su usuario administrador
              (tenant_admin)
                      │
                      ▼
             Inicia sesión
                      │
                      ▼
        ┌─────────────────────────────┐
        │ Administrador del Tenant    │
        └──────────────┬──────────────┘
                       │
             ┌─────────┼─────────┐
             ▼         ▼         ▼
          Usuarios   Displays  Publicidades
                       │         │
                       └────┬────┘
                            ▼
                   Asociar publicidad
                   con un display
                            │
                            ▼
                    Crear programación
                            │
                            ▼
                    Publicar contenido
                            │
                            ▼
                     Reproducción
                      en el tótem
                            │
                            ▼
                      Generación
                     de métricas
                            │
                            ▼
                           FIN
```

Este flujo representa el proceso principal que la plataforma busca administrar.

---

# 5. Actores y stakeholders

## 5.1 SuperAdmin

Representa al propietario o administrador global de la plataforma SaaS.

### Responsabilidades

* Administrar los tenants.
* Administrar usuarios de la plataforma.
* Crear administradores para los tenants.
* Supervisar la información global del sistema.
* Administrar permisos y configuraciones generales.
* Acceder a información de los diferentes tenants según las políticas definidas.

El SuperAdmin pertenece al nivel global del sistema y utiliza `tenant_id = NULL`.

---

## 5.2 Tenant Admin

Representa al administrador de una empresa cliente.

Por ejemplo:

```text
Coca-Cola
    │
    └── coca-cola@gmail.com
          └── tenant_admin
```

### Responsabilidades

* Administrar los usuarios de su empresa.
* Crear usuarios internos.
* Administrar displays.
* Administrar publicidades.
* Programar publicidades.
* Administrar menús.
* Consultar las métricas disponibles.

Cada tenant puede tener como máximo un usuario con el rol `tenant_admin`.

---

## 5.3 Usuario

Representa a un usuario operativo perteneciente a una empresa cliente.

### Responsabilidades

Dependiendo de los permisos asignados:

* Consultar displays.
* Administrar publicidades.
* Administrar menús.
* Administrar elementos de menú.
* Administrar horarios.
* Consultar analíticas.

Sus operaciones están restringidas al tenant al que pertenece.

---

## 5.4 Tótem / Display

Es el dispositivo físico que reproduce el contenido administrado desde la plataforma.

No representa necesariamente a una persona, sino a un componente operativo del sistema.

Su función principal es:

```text
Servidor
   │
   ▼
Contenido configurado
   │
   ▼
Display / Tótem
   │
   ▼
Reproducción
```

---

# 6. Situación actual y evidencia

## 6.1 Elementos disponibles

Actualmente el proyecto cuenta con:

* Diseño y planificación inicial del sistema.
* Diagramas preliminares.
* Backlog de funcionalidades.
* Diseño de base de datos.
* Backend desarrollado con Laravel 12.
* Frontend basado en React + TypeScript + Inertia.js.
* Base de datos MySQL.
* Sistema de autenticación.
* Sistema de roles y permisos mediante Spatie Laravel Permission.
* Policies para autorización.
* Arquitectura multi-tenant mediante `tenant_id`.
* Global Scopes para aislamiento de información.
* CRUDs principales del sistema.
* Validaciones mediante Form Requests.
* Seeders para roles, permisos y SuperAdmin.
* Migraciones funcionales.
* Estructura inicial para gestión de publicidad, displays, menús, programación y analíticas.

## 6.2 Elementos pendientes

El desarrollo continúa en diferentes áreas, entre ellas:

* Finalización de las interfaces frontend.
* Integración completa entre frontend y funcionalidades del backend.
* Implementación y validación de comunicación en tiempo real.
* Implementación completa del cliente PWA para los displays.
* Implementación completa del almacenamiento y distribución de contenido multimedia.
* Implementación y validación de analíticas.
* Pruebas funcionales completas.
* Pruebas de seguridad y aislamiento multi-tenant.
* Despliegue en infraestructura de producción.
* Documentación técnica y funcional completa.

Estas funcionalidades representan parte del trabajo pendiente y no deben considerarse completamente terminadas hasta contar con sus respectivas pruebas.

---

# 7. Definición del problema

## 7.1 Problema central

> **Dificultad para administrar de manera centralizada, segura y organizada múltiples tótems publicitarios, sus contenidos, horarios, usuarios y métricas, especialmente cuando diferentes empresas utilizan la misma plataforma.**

---

# 8. Causas del problema

## 8.1 Causas operativas

* Administración manual del contenido.
* Falta de una plataforma centralizada.
* Dificultad para controlar diferentes dispositivos.
* Actualización manual de campañas.
* Falta de automatización en la programación.

## 8.2 Causas de gestión

* Ausencia de una administración central de usuarios.
* Falta de separación clara de responsabilidades.
* Dificultad para controlar qué usuarios pueden realizar determinadas operaciones.
* Falta de información consolidada para la toma de decisiones.

## 8.3 Causas técnicas

* Falta de un sistema centralizado para gestionar dispositivos.
* Falta de aislamiento adecuado entre clientes.
* Falta de mecanismos automatizados para programar contenido.
* Ausencia de métricas centralizadas.
* Dependencia de procesos manuales para administrar contenido digital.

---

# 9. Efectos del problema

Las causas anteriores pueden generar:

* Mayor tiempo necesario para administrar los dispositivos.
* Errores en la publicación de contenido.
* Publicidad reproducida fuera del horario previsto.
* Dificultad para administrar múltiples clientes.
* Riesgo de acceso a información perteneciente a otro cliente.
* Falta de control sobre los usuarios.
* Dificultad para conocer el rendimiento de las campañas.
* Mayor costo operativo.
* Dificultad para escalar el servicio.

---

# 10. Árbol de problemas

```text
                         EFECTOS
                            │
       ┌────────────────────┼────────────────────┐
       │                    │                    │
 Mayor costo          Errores en          Dificultad para
 operativo            publicidad          escalar el servicio
       │                    │                    │
       └────────────────────┼────────────────────┘
                            │
                            ▼
                  PROBLEMA CENTRAL
                            │
          Dificultad para administrar de forma
          centralizada, segura y organizada
          múltiples tótems y su contenido
                            │
       ┌────────────────────┼────────────────────┐
       │                    │                    │
       ▼                    ▼                    ▼
   Causas                Causas               Causas
  operativas            gestión              técnicas
       │                    │                    │
       ▼                    ▼                    ▼
 Procesos manuales   Falta de control     Falta de plataforma
 Actualización       de usuarios          centralizada
 manual              Falta de métricas    Falta de aislamiento
 Falta de            Falta de             multi-tenant
 automatización      centralización       Falta de programación
```

---

# 11. Métricas y decisiones apoyadas

Uno de los objetivos de la plataforma es transformar los datos generados por los dispositivos y usuarios en información útil para tomar decisiones.

| KPI / Métrica                 | Pregunta que responde                           | Decisión que permite apoyar                  |
| ----------------------------- | ----------------------------------------------- | -------------------------------------------- |
| Reproducciones por publicidad | ¿Cuántas veces se reprodujo una publicidad?     | Evaluar alcance de una campaña               |
| Reproducciones por display    | ¿Qué dispositivos reproducen más contenido?     | Identificar displays de mayor actividad      |
| Interacciones con menús       | ¿Qué contenidos generan mayor interacción?      | Optimizar contenido interactivo              |
| Actividad por período         | ¿En qué horarios existe mayor actividad?        | Ajustar horarios de programación             |
| Publicidades activas          | ¿Qué campañas están actualmente disponibles?    | Controlar campañas vigentes                  |
| Publicidades por display      | ¿Qué contenido tiene asignado cada dispositivo? | Verificar la distribución de campañas        |
| Usuarios por tenant           | ¿Cuántos usuarios administra cada empresa?      | Gestionar accesos y responsabilidades        |
| Estado de displays            | ¿Qué dispositivos están activos o disponibles?  | Detectar dispositivos que requieren atención |
| Actividad por tenant          | ¿Qué nivel de utilización tiene cada cliente?   | Supervisar el uso de la plataforma           |

---

# 12. Decisiones que el sistema busca facilitar

La plataforma permitirá disponer de información para apoyar decisiones como:

### Decisiones operativas

* Qué publicidad debe reproducirse.
* En qué display debe reproducirse.
* En qué horario debe ejecutarse.
* Qué contenido debe actualizarse.
* Qué dispositivos requieren atención.

### Decisiones administrativas

* Qué usuarios deben tener acceso.
* Qué permisos debe poseer cada usuario.
* Qué empresa administra determinados dispositivos.
* Qué campañas están activas.

### Decisiones estratégicas

* Qué campañas tienen mayor actividad.
* Qué displays presentan mayor utilización.
* Qué contenidos generan mayor interacción.
* Cómo optimizar la programación de publicidad.
* Cómo escalar la plataforma hacia nuevos clientes.

---

# 13. Alcance inicial

El sistema se plantea inicialmente como una plataforma SaaS capaz de administrar múltiples empresas desde una misma aplicación.

Cada empresa posee un entorno independiente compuesto por:

```text
TENANT
 │
 ├── Usuarios
 │
 ├── Displays / Tótems
 │
 ├── Publicidades
 │
 ├── Programaciones
 │
 ├── Menús
 │    └── Elementos de menú
 │
 └── Analíticas
```

El aislamiento de información constituye una característica fundamental de la plataforma, ya que un cliente no debe poder acceder a los recursos pertenecientes a otro cliente.

---

# 14. Visión del producto

La visión del proyecto es construir una plataforma SaaS que permita pasar de una administración manual y distribuida de tótems publicitarios a un modelo centralizado, automatizado y escalable.

El sistema deberá permitir que una empresa contrate el servicio, obtenga su propio entorno dentro de la plataforma y pueda administrarlo de forma independiente, mientras que el propietario del SaaS conserva una administración global mediante el rol `superadmin`.

El modelo esperado es:

```text
                         PLATAFORMA SaaS
                               │
                 ┌─────────────┴─────────────┐
                 │                           │
             SUPERADMIN                 CLIENTES
                 │                           │
        Administración global       ┌────────┼────────┐
                                    │        │        │
                                 Tenant A Tenant B Tenant C
                                    │        │        │
                                 Usuarios  Usuarios Usuarios
                                 Displays  Displays Displays
                                 Ads       Ads      Ads
                                 Menús     Menús    Menús
```

Esta arquitectura permite que el sistema pueda crecer en cantidad de clientes y dispositivos sin necesidad de crear una aplicación independiente para cada empresa.
