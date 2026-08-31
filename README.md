# Totems SaaS

Plataforma SaaS para la **gestión centralizada de contenido publicitario e interactivo en displays digitales (totems)**.

El sistema permite a diferentes empresas administrar sus propios displays, publicidades, menús y programación desde un entorno aislado por **tenant**, manteniendo separación de datos y control de acceso mediante roles y permisos.

---

## 📌 Descripción

**Totems SaaS** es una plataforma web multi-tenant orientada a empresas que utilizan displays digitales para mostrar contenido publicitario e interactivo.

La plataforma centraliza la administración de:

* Empresas clientes (tenants).
* Usuarios y roles.
* Displays.
* Publicidades.
* Asociaciones entre publicidades y displays.
* Menús interactivos.
* Elementos de menú.
* Programación de publicidades.
* Analítica y eventos de interacción.
* Funcionamiento parcialmente offline mediante PWA.

El objetivo es proporcionar a cada empresa un entorno independiente desde el cual pueda administrar sus dispositivos y contenido, mientras que el propietario de la plataforma mantiene una visión global mediante el rol **SuperAdmin**.

---

## 🎯 Objetivo del proyecto

Desarrollar una plataforma SaaS que permita administrar de manera centralizada y segura el contenido mostrado en displays digitales, proporcionando:

* Gestión independiente por empresa.
* Control de usuarios y permisos.
* Administración de contenido publicitario.
* Programación de contenido.
* Menús interactivos.
* Estadísticas de utilización.
* Aislamiento de información entre clientes.
* Soporte para funcionamiento parcial sin conexión.

---

## 🏗️ Arquitectura

El sistema utiliza una arquitectura web basada en **Laravel + React + Inertia.js**.

```text
                    ┌─────────────────────┐
                    │      SUPERADMIN     │
                    │ Administración SaaS │
                    └──────────┬──────────┘
                               │
                               ▼
┌──────────────────────────────────────────────────┐
│                    BACKEND                       │
│                  Laravel 12                      │
│                                                  │
│  Auth │ Policies │ Roles │ Tenancy │ API/Routes │
└────────────────────────┬─────────────────────────┘
                         │
                         ▼
                ┌─────────────────┐
                │      MySQL      │
                │   Base de datos │
                └─────────────────┘
                         ▲
                         │
┌────────────────────────┴─────────────────────────┐
│                   FRONTEND                       │
│                React + Inertia.js                │
│                                                  │
│  Dashboard │ CRUD │ Menús │ Displays │ Analytics │
└────────────────────────┬─────────────────────────┘
                         │
                         ▼
                 ┌───────────────┐
                 │    Displays   │
                 │     Totems    │
                 └───────────────┘
```

### Multi-tenancy

El sistema utiliza un modelo de **multi-tenancy basado en `tenant_id`**.

Cada empresa cliente posee un tenant independiente.

```text
                    Plataforma SaaS
                           │
          ┌────────────────┼────────────────┐
          │                │                │
          ▼                ▼                ▼
      Tenant A         Tenant B         Tenant C
          │                │                │
       Usuarios         Usuarios         Usuarios
       Displays         Displays         Displays
       Anuncios         Anuncios         Anuncios
       Menús            Menús            Menús
```

La separación de información se implementa mediante:

* `tenant_id`.
* Global Scopes.
* Policies.
* Validaciones en Requests.
* Roles y permisos.

---

## 👥 Roles del sistema

La plataforma utiliza tres roles principales:

| Rol            | Descripción                                                                                   |
| -------------- | --------------------------------------------------------------------------------------------- |
| `superadmin`   | Propietario de la plataforma. Tiene acceso global a todos los tenants y funcionalidades.      |
| `tenant_admin` | Administrador de una empresa cliente. Gestiona usuarios y recursos de su propio tenant.       |
| `user`         | Usuario operativo de una empresa. Sus operaciones están limitadas por los permisos asignados. |

### Flujo de acceso

```text
SuperAdmin
    │
    ├── Todos los tenants
    ├── Todos los usuarios
    ├── Todos los displays
    ├── Todas las publicidades
    └── Analítica global


Cliente
    │
    └── Tenant propio
          │
          ├── Usuarios
          ├── Displays
          ├── Publicidades
          ├── Menús
          └── Analítica propia
```

---

## 🧩 Stack tecnológico

### Backend

| Tecnología                    | Uso                                      |
| ----------------------------- | ---------------------------------------- |
| **Laravel 12**                | Framework principal del backend          |
| **PHP 8.3+**                  | Lenguaje de programación                 |
| **MySQL**                     | Sistema gestor de base de datos          |
| **Eloquent ORM**              | Acceso y relaciones con la base de datos |
| **Laravel Breeze**            | Autenticación                            |
| **Spatie Laravel Permission** | Roles y permisos                         |
| **Laravel Policies**          | Autorización y control de acceso         |

### Frontend

| Tecnología                  | Uso                               |
| --------------------------- | --------------------------------- |
| **React**                   | Construcción de interfaces        |
| **TypeScript**              | Tipado estático                   |
| **Inertia.js**              | Integración entre Laravel y React |
| **Tailwind CSS**            | Estilos e interfaz                |
| **@dnd-kit/core**           | Drag & Drop para menús            |
| **Leaflet / React Leaflet** | Mapas y ubicación de displays     |

### PWA / Offline

Para el funcionamiento parcial sin conexión se contempla:

* Service Worker.
* Workbox.
* Cache Storage.
* IndexedDB.

### Comunicación en tiempo real

Se contempla el uso de:

* Laravel Reverb.
* WebSockets.

---

## ⚙️ Funcionalidades principales

### 🔐 Autenticación y seguridad

* Registro de empresas.
* Inicio de sesión.
* Cierre de sesión.
* Gestión de usuarios.
* Roles.
* Permisos.
* Policies.
* Control de acceso por tenant.
* Aislamiento de información entre empresas.

### 🏢 Gestión de empresas

Cada empresa registrada posee su propio tenant.

```text
Empresa
   │
   ├── Usuarios
   ├── Displays
   ├── Publicidades
   ├── Menús
   ├── Programaciones
   └── Analítica
```

### 👤 Gestión de usuarios

Los administradores pueden gestionar los usuarios pertenecientes a su empresa.

El sistema controla:

* Nombre.
* Email.
* Código.
* Contraseña.
* Estado.
* Rol.
* Tenant.

Un tenant puede tener **un único `tenant_admin`**.

### 📺 Gestión de displays

Permite registrar y administrar los dispositivos donde se mostrará el contenido.

### 📢 Gestión de publicidades

Permite:

* Crear publicidades.
* Modificar publicidades.
* Activar/desactivar publicidades.
* Definir duración.
* Asociar publicidades a displays.
* Programar horarios.

### 🍔 Menús interactivos

Permite construir estructuras de contenido mediante:

* Menús.
* Elementos de menú.
* Submenús.
* Ordenamiento de elementos.

### 📅 Programación

Permite definir cuándo una publicidad debe mostrarse mediante:

* Día de la semana.
* Hora de inicio.
* Hora de finalización.
* Fecha de inicio.
* Fecha de finalización.

### 📊 Analítica

La plataforma contempla dos niveles de analítica:

**Cliente**

```text
Solo información de su tenant
```

**SuperAdmin**

```text
Información global de toda la plataforma
```

---

## 🗂️ Estructura principal del proyecto

```text
totems-saas/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Requests/
│   │   └── Middleware/
│   │
│   ├── Models/
│   ├── Policies/
│   ├── Scopes/
│   └── Traits/
│
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
│
├── resources/
│   ├── js/
│   │   ├── Components/
│   │   ├── Layouts/
│   │   ├── Pages/
│   │   └── types/
│   │
│   └── css/
│
├── routes/
│   ├── web.php
│   └── auth.php
│
├── storage/
├── tests/
├── .env.example
├── composer.json
├── package.json
└── README.md
```

---

## 🗄️ Modelo general de datos

Las principales entidades del sistema son:

```text
Tenant
  │
  ├── Users
  ├── Displays
  ├── Advertisements
  ├── Menus
  └── Analytics Events

Display
  │
  └── DisplayAdvertisement
          │
          ├── Advertisement
          └── AdSchedule

Menu
  │
  └── MenuItem
```

Las relaciones se encuentran implementadas mediante **Eloquent ORM** y claves foráneas en MySQL.

---

## 🚀 Instalación

### Requisitos

Antes de ejecutar el proyecto se requiere:

* PHP 8.3 o superior.
* Composer.
* Node.js.
* npm.
* MySQL.
* Git.

### 1. Clonar el repositorio

```bash
git clone <URL_DEL_REPOSITORIO>
cd totems-saas
```

### 2. Instalar dependencias PHP

```bash
composer install
```

### 3. Instalar dependencias frontend

```bash
npm install
```

### 4. Crear archivo `.env`

```bash
cp .env.example .env
```

Configurar las credenciales de MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=totmes_saas
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Generar la clave de aplicación

```bash
php artisan key:generate
```

### 6. Ejecutar migraciones y seeders

```bash
php artisan migrate:fresh --seed
```

Esto crea la estructura de la base de datos y carga:

* Permisos.
* Roles.
* Usuario SuperAdmin.

### 7. Compilar frontend

Para desarrollo:

```bash
npm run dev
```

### 8. Ejecutar Laravel

En otra terminal:

```bash
php artisan serve
```

La aplicación estará disponible normalmente en:

```text
http://127.0.0.1:8000
```

---

## 🔑 Usuario SuperAdmin

El sistema crea un usuario inicial mediante `SuperAdminSeeder`.

Las credenciales utilizadas durante el desarrollo deben mantenerse fuera del repositorio y configurarse mediante variables de entorno antes de utilizar el sistema en producción.

> **Importante:** nunca publicar contraseñas reales en GitHub.

---

## 📚 Documentación

La documentación completa del proyecto se encuentra organizada dentro de la carpeta:

```text
docs/
```

Documentación inicial:

```text
docs/
│
├── 01. Contexto y diagnóstico inicial.md
├── 02. MVP y propuesta de valor.md
├── 03. Requisitos e Historias de Usuario.md
└── 07. Pivot & Plan - Ready to Sprint.md
```

Posteriormente se documentarán las historias de usuario y su implementación individual:

```text
docs/
└── historias/
    ├── HU-01.md
    ├── HU-02.md
    ├── HU-03.md
    └── ...
```

Cada historia podrá incluir:

* Objetivo.
* Criterios de aceptación.
* Archivos modificados.
* Migraciones utilizadas.
* Modelos.
* Controllers.
* Requests.
* Policies.
* Componentes frontend.
* Pruebas realizadas.
* Evidencia de funcionamiento.

---


## 🔒 Seguridad y aislamiento de datos

Uno de los principios fundamentales del proyecto es evitar que un cliente pueda acceder a información perteneciente a otro cliente.

El control se realiza mediante diferentes capas:

```text
Request
   ↓
Authorization
   ↓
Policy
   ↓
Tenant Scope
   ↓
Eloquent
   ↓
MySQL
```

La aplicación no depende únicamente de la interfaz para restringir información. Las restricciones se aplican en el backend.

---

## 🧪 Estado actual

**Estado:** En desarrollo.

Actualmente se encuentra implementada y validada la base de:

* Arquitectura Laravel + React + Inertia.
* Base de datos MySQL.
* Autenticación.
* Roles y permisos.
* Multi-tenancy.
* Gestión de usuarios.
* Policies.
* Validaciones.
* Estructura inicial de publicidad, displays, menús y programación.

El desarrollo continúa con los módulos funcionales restantes del MVP.

---

## 👨‍💻 Equipo

| Integrante     | Rol                                  |
| -------------- | ------------------------------------ |
| Javier Aramayo | Desarrollo / Análisis / Arquitectura |

---
