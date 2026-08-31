# 02. MVP y propuesta de valor

## 1. Árbol de Soluciones

El árbol de soluciones representa la transformación de los problemas identificados en el diagnóstico inicial en soluciones concretas que serán proporcionadas por el sistema.

```text
                           RESULTADO ESPERADO
                    Gestión centralizada y eficiente
                    de contenido publicitario digital
                                  │
              ┌───────────────────┼───────────────────┐
              │                   │                   │
       Mayor control        Mejor organización    Información para
       de los tótems        de publicidad         toma de decisiones
              │                   │                   │
              └───────────────────┼───────────────────┘
                                  │
                         SOLUCIÓN PRINCIPAL
                                  │
              ┌───────────────────┼───────────────────┐
              │                   │                   │
       Gestión de usuarios   Gestión de anuncios   Gestión de tótems
       y permisos            y programación        y displays
              │                   │                   │
              ├───────────────────┼───────────────────┤
              │                   │                   │
        Multi-tenancy       Menús y contenido     Analítica
        por empresa         interactivo           de eventos
              │                   │                   │
              └───────────────────┼───────────────────┘
                                  │
                              MVP SaaS
                   para gestión de tótems
                         publicitarios
```

La solución propuesta consiste en una plataforma SaaS multi-tenant que permita a diferentes empresas administrar sus recursos publicitarios desde un entorno aislado y centralizado.

---

# 2. Visión del Producto y Propuesta de Valor

## 2.1 Visión del producto

> Para empresas que utilizan tótems digitales para mostrar publicidad y contenido interactivo, **Totems SaaS** es una plataforma web de gestión de contenido y dispositivos que permite administrar usuarios, tótems, publicidad, programación, menús y métricas desde un único entorno centralizado, a diferencia de una gestión manual y distribuida de los dispositivos y contenidos.

## 2.2 Propuesta de valor

El sistema busca simplificar la administración de múltiples tótems digitales proporcionando una plataforma central desde la cual cada empresa pueda gestionar sus dispositivos y contenido publicitario.

La plataforma utiliza un modelo **SaaS multi-tenant**, donde cada empresa dispone de su propio entorno lógico y sus datos permanecen aislados de los demás clientes.

### Beneficios principales

* Centralización de la administración de tótems.
* Administración de publicidad desde una interfaz web.
* Programación de anuncios según días y horarios.
* Gestión de contenido interactivo mediante menús.
* Administración de usuarios y roles.
* Separación de información entre empresas.
* Visualización de métricas de utilización.
* Reducción de tareas manuales.
* Mayor control sobre qué contenido se muestra en cada dispositivo.
* Posibilidad de administrar múltiples dispositivos desde un único sistema.

---

# 3. Matriz Es / No es / Hace / No hace

| Categoría   | Definición                                                                                   |
| ----------- | -------------------------------------------------------------------------------------------- |
| **Es**      | Una plataforma SaaS web para administrar tótems digitales, publicidad, contenido y usuarios. |
| **Es**      | Un sistema multi-tenant para múltiples empresas.                                             |
| **Es**      | Una plataforma de administración centralizada.                                               |
| **Es**      | Un sistema con control de acceso mediante roles y permisos.                                  |
| **No es**   | Un sistema de fabricación o mantenimiento físico de tótems.                                  |
| **No es**   | Una agencia de publicidad.                                                                   |
| **No es**   | Una plataforma de compra y venta de espacios publicitarios.                                  |
| **No es**   | Un sistema contable o financiero.                                                            |
| **No es**   | Un sistema de gestión empresarial completo ERP.                                              |
| **Hace**    | Permite administrar empresas, usuarios y permisos.                                           |
| **Hace**    | Permite registrar y administrar displays/tótems.                                             |
| **Hace**    | Permite crear y administrar publicidad.                                                      |
| **Hace**    | Permite asociar publicidad con displays.                                                     |
| **Hace**    | Permite programar cuándo debe mostrarse determinada publicidad.                              |
| **Hace**    | Permite crear menús y elementos interactivos.                                                |
| **Hace**    | Registra eventos para obtener métricas.                                                      |
| **No hace** | No controla directamente el hardware físico del tótem desde el MVP.                          |
| **No hace** | No realiza facturación automática de clientes.                                               |
| **No hace** | No incluye un sistema avanzado de inteligencia artificial para generar publicidad.           |
| **No hace** | No administra campañas publicitarias externas como Google Ads o Meta Ads.                    |

---

# 4. Canvas del MVP

| Elemento                 | Definición                                                                                                                       |
| ------------------------ | -------------------------------------------------------------------------------------------------------------------------------- |
| **Usuarios**             | SuperAdmin del SaaS, administradores de empresas y usuarios pertenecientes a cada empresa.                                       |
| **Problema**             | Dificultad para centralizar y controlar publicidad, contenido, dispositivos y usuarios de múltiples tótems.                      |
| **Propuesta**            | Plataforma SaaS centralizada para administrar tótems, publicidad, programación, contenido y métricas.                            |
| **Flujo principal**      | Empresa → Usuario administrador → Tótems → Publicidad/Contenido → Programación → Visualización → Registro de eventos → Métricas. |
| **Funciones mínimas**    | Autenticación, multi-tenancy, roles, usuarios, displays, publicidad, programación, menús y analítica básica.                     |
| **Resultados esperados** | Mayor control, menor esfuerzo administrativo y mejor organización del contenido digital.                                         |
| **Métricas**             | Cantidad de anuncios, displays activos, eventos registrados, interacciones y utilización de contenido.                           |
| **Riesgos**              | Errores de aislamiento entre tenants, pérdida de conectividad, problemas de sincronización y crecimiento del volumen de datos.   |
| **Restricciones**        | El MVP se concentra en la gestión web y no pretende cubrir todos los procesos comerciales o físicos relacionados con los tótems. |
| **Fuera del MVP**        | Facturación, mantenimiento físico, marketplace publicitario, IA avanzada y funcionalidades empresariales no esenciales.          |

---

# 5. Alcance Funcional Detallado

## 5.1 Identidad y acceso

El sistema contará con mecanismos para controlar el acceso de los usuarios.

### Funcionalidades

* Inicio de sesión.
* Cierre de sesión.
* Gestión de usuarios.
* Gestión de roles.
* Gestión de permisos.
* Control de acceso mediante políticas.
* Protección de rutas.
* Separación de usuarios por tenant.

### Roles principales

#### SuperAdmin

Usuario propietario de la plataforma SaaS.

Tiene acceso global al sistema y puede administrar los diferentes tenants.

* Gestionar tenants.
* Gestionar usuarios.
* Gestionar displays.
* Gestionar publicidad.
* Gestionar menús.
* Consultar analítica.
* Administrar permisos globales.

#### Tenant Admin

Administrador de una empresa cliente.

Puede administrar los recursos pertenecientes exclusivamente a su tenant.

* Gestionar usuarios de su empresa.
* Gestionar displays.
* Gestionar publicidad.
* Programar publicidad.
* Gestionar menús.
* Consultar métricas permitidas.

Cada tenant tendrá como máximo un usuario con el rol `tenant_admin`.

#### User

Usuario operativo perteneciente a una empresa.

Sus capacidades estarán determinadas por los permisos asignados y estarán limitadas al tenant correspondiente.

---

# 5.2 Multi-tenancy

El sistema utilizará un modelo de **multi-tenancy lógico basado en `tenant_id`**.

Cada empresa tendrá un tenant independiente dentro de la misma aplicación y base de datos.

El aislamiento se realizará mediante:

* `tenant_id`.
* Global Scopes.
* Relaciones Eloquent.
* Policies.
* Validaciones.
* Control de autorización.

El objetivo es impedir que un usuario perteneciente a una empresa pueda consultar o modificar información de otra empresa.

---

# 5.3 Gestión de Displays/Tótems

El sistema permitirá registrar los dispositivos utilizados por cada empresa.

Cada display estará asociado a un tenant.

Las operaciones principales serán:

* Registrar display.
* Consultar displays.
* Modificar información.
* Activar o desactivar displays.
* Asociar contenido publicitario.
* Consultar información relacionada con publicidad.

---

# 5.4 Gestión de publicidad

Las empresas podrán administrar el contenido publicitario que será mostrado en sus displays.

Las operaciones principales serán:

* Crear publicidad.
* Modificar publicidad.
* Consultar publicidad.
* Activar o desactivar publicidad.
* Definir tipo de contenido.
* Definir duración.
* Definir periodo de disponibilidad.
* Asociar publicidad con displays.

El MVP contempla inicialmente contenido de tipo:

* Imagen.
* Video.

---

# 5.5 Programación de publicidad

El sistema permitirá establecer cuándo debe mostrarse una publicidad determinada.

La programación podrá considerar:

* Día de la semana.
* Hora de inicio.
* Hora de finalización.
* Fecha de inicio.
* Fecha de finalización.
* Display asociado.

Esto permitirá que diferentes anuncios puedan ejecutarse según una planificación determinada.

---

# 5.6 Gestión de menús y contenido interactivo

Los tótems podrán utilizar menús para presentar contenido interactivo.

El sistema permitirá:

* Crear menús.
* Modificar menús.
* Activar o desactivar menús.
* Crear elementos dentro de los menús.
* Organizar elementos.
* Crear estructuras jerárquicas mediante elementos padre e hijo.
* Asociar contenido a los elementos correspondientes.

---

# 5.7 Analítica

El sistema registrará eventos relacionados con la utilización del contenido.

Estos eventos permitirán generar información para conocer el comportamiento de los usuarios y la utilización de los contenidos.

Algunos indicadores contemplados son:

* Visualizaciones.
* Interacciones.
* Eventos por display.
* Eventos por publicidad.
* Eventos por menú.
* Eventos por periodo.

---

# 6. Objetivos

## 6.1 Objetivo general

Desarrollar una plataforma SaaS multi-tenant para centralizar la administración de tótems digitales, permitiendo a las empresas gestionar sus usuarios, displays, publicidad, programación, contenido interactivo y métricas desde una única plataforma web.

## 6.2 Objetivos específicos

1. Implementar un sistema de autenticación y autorización basado en roles y permisos.

2. Implementar una arquitectura multi-tenant que permita aislar los datos de cada empresa.

3. Desarrollar la gestión de displays pertenecientes a cada empresa.

4. Implementar la creación, modificación y administración de contenido publicitario.

5. Implementar la programación de publicidad según fechas, días y horarios.

6. Implementar la gestión de menús y contenido interactivo.

7. Registrar eventos de utilización para generar métricas básicas.

8. Proporcionar una interfaz web que permita administrar los recursos de forma centralizada.

9. Garantizar mediante políticas y validaciones que los usuarios solamente puedan acceder a los recursos permitidos.

---

# 7. Objetivo SMART

> **Desarrollar e implementar durante el periodo establecido para el proyecto una plataforma web SaaS multi-tenant funcional que permita a empresas administrar usuarios, displays, publicidad, programación, menús y métricas, garantizando el aislamiento de datos entre tenants y validando el funcionamiento de los principales flujos mediante pruebas funcionales antes de la entrega del MVP.**

### Desglose SMART

| Criterio           | Aplicación                                                                                      |
| ------------------ | ----------------------------------------------------------------------------------------------- |
| **S - Específico** | Desarrollar una plataforma SaaS para gestionar tótems, publicidad, usuarios y contenido.        |
| **M - Medible**    | El MVP deberá implementar los módulos definidos y superar las pruebas funcionales establecidas. |
| **A - Alcanzable** | Se utilizarán tecnologías conocidas y una arquitectura modular basada en Laravel y React.       |
| **R - Relevante**  | Resuelve la necesidad de centralizar y controlar la administración de tótems digitales.         |
| **T - Temporal**   | El desarrollo se realizará dentro del periodo establecido para el proyecto académico.           |

---

# 8. Criterios de Éxito e Hipótesis

## 8.1 Hipótesis

> **Si las empresas disponen de una plataforma centralizada para administrar sus tótems, publicidad, programación, usuarios y contenido interactivo, entonces podrán reducir el esfuerzo necesario para gestionar sus dispositivos y tendrán mayor control sobre el contenido que se muestra en cada uno de ellos.**

Además, se busca validar que el modelo SaaS multi-tenant sea adecuado para permitir que diferentes empresas utilicen la misma plataforma manteniendo sus datos aislados.

---

## 8.2 Criterios de éxito del MVP

El MVP será considerado exitoso cuando se cumplan las siguientes condiciones:

### Autenticación

* [ ] Un usuario puede iniciar sesión correctamente.
* [ ] Un usuario puede cerrar sesión.
* [ ] Las rutas protegidas requieren autenticación.
* [ ] Los usuarios sin permisos no pueden acceder a funcionalidades restringidas.

### Multi-tenancy

* [ ] Cada empresa posee su propio tenant.
* [ ] Los usuarios pertenecen a un tenant.
* [ ] Un usuario de un tenant no puede consultar información de otro tenant.
* [ ] El SuperAdmin puede acceder globalmente.
* [ ] Las políticas y scopes mantienen el aislamiento de datos.

### Usuarios

* [ ] El SuperAdmin puede crear usuarios.
* [ ] Un Tenant Admin puede crear usuarios dentro de su empresa.
* [ ] Solo puede existir un `tenant_admin` por tenant.
* [ ] Un Tenant Admin no puede crear otro Tenant Admin.
* [ ] Los usuarios pueden ser activados o desactivados.
* [ ] El SuperAdmin no puede ser desactivado accidentalmente.

### Displays

* [ ] Se pueden registrar displays.
* [ ] Cada display pertenece a un tenant.
* [ ] Los usuarios solamente pueden administrar displays de su tenant.

### Publicidad

* [ ] Se pueden crear anuncios.
* [ ] Se pueden modificar anuncios.
* [ ] Se pueden activar y desactivar anuncios.
* [ ] Los anuncios pueden asociarse a displays.
* [ ] La publicidad permanece aislada por tenant.

### Programación

* [ ] Se pueden crear horarios para publicidad.
* [ ] Se puede definir día y horario.
* [ ] Se puede establecer una fecha de inicio y finalización.
* [ ] Los horarios solamente pueden administrarse dentro del tenant correspondiente.

### Menús

* [ ] Se pueden crear menús.
* [ ] Se pueden crear elementos de menú.
* [ ] Los elementos pueden organizarse jerárquicamente.
* [ ] Los menús pertenecen a un tenant.

### Analítica

* [ ] Se pueden registrar eventos.
* [ ] Los eventos pueden asociarse con los recursos correspondientes.
* [ ] Los datos pueden utilizarse para generar métricas básicas.

### Validación general

* [ ] Las migraciones ejecutan correctamente mediante `php artisan migrate:fresh`.
* [ ] Los seeders inicializan correctamente los roles y permisos.
* [ ] Las operaciones principales funcionan mediante pruebas funcionales.
* [ ] No existen accesos entre tenants.
* [ ] Las operaciones no autorizadas son rechazadas correctamente.
* [ ] Los flujos principales del MVP pueden ejecutarse de principio a fin.

---

# 9. Límites del MVP

Para evitar un crecimiento descontrolado del alcance, las siguientes funcionalidades quedan fuera de la primera versión:

* Facturación y cobros automáticos.
* Suscripciones y planes comerciales avanzados.
* Marketplace de publicidad.
* Integración con plataformas externas de publicidad.
* Inteligencia artificial para generación automática de contenido.
* Gestión de inventario de hardware.
* Sistema avanzado de CRM.
* Contabilidad.
* Analítica avanzada mediante machine learning.
* Gestión empresarial ERP.
* Funcionalidades que no sean necesarias para validar el flujo principal del producto.

Estas funcionalidades podrán evaluarse posteriormente como parte de futuras versiones del producto.
