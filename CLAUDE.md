# CLAUDE.md — Guía del proyecto reuniones-jw

Este archivo define las convenciones, decisiones de arquitectura y restricciones
que cualquier asistente de IA (o desarrollador) debe respetar al trabajar en este
proyecto. Todas las decisiones listadas están **aprobadas**.

---

## 1. Descripción general

Aplicación web **multi-congregación** para la gestión de congregaciones:
usuarios, roles, permisos, horarios, programación semanal, asignaciones y
discursos. Debe estar preparada para crecer con nuevos módulos sin cambiar la
arquitectura base.

---

## 2. Stack tecnológico obligatorio

| Capa                 | Tecnología                          |
|----------------------|-------------------------------------|
| Backend              | PHP 8.2+                            |
| Framework            | Laravel 12                          |
| Base de datos        | MySQL                              |
| Servidor web         | Apache                             |
| Entorno local        | XAMPP                              |
| RBAC                 | spatie/laravel-permission          |
| PDF                  | barryvdh/laravel-dompdf            |
| Frontend (CSS)       | Bootstrap 5                        |
| Tipografía           | Google Sans Flex                   |
| Control de versiones | GitHub (repo: `reuniones-jw`)      |

> **Interfaz de usuario:** la capa visual usa **Bootstrap 5** como framework CSS
> y **Google Sans Flex** como tipografía base. (Decisión aprobada; las vistas se
> construyen en una capa posterior al backend.)

---

## 3. Roles y permisos

- Usar **spatie/laravel-permission**. **NO** implementar un sistema RBAC propio.
- Utilizar las tablas y convenciones estándar de Spatie.
- Roles iniciales: `SuperAdministrador`, `AdministradorCongregacion`, `Usuario`.
- El sistema debe permitir agregar nuevos roles y permisos en el futuro sin
  modificar la arquitectura.
- **Spatie Teams**: NO se activa por ahora. Cada usuario pertenece a una sola
  congregación.

### Catálogo de permisos

Convención de nombres: `modulo.accion` (en inglés, estilo punto). Catálogo actual:

| Módulo        | Permisos                                                                                  |
|---------------|-------------------------------------------------------------------------------------------|
| Congregations | `congregations.view`, `congregations.create`, `congregations.update`, `congregations.toggle-status` |
| Users         | `users.view`, `users.create`, `users.update`, `users.toggle-status`, `users.reset-password` |
| Roles         | `roles.view`, `roles.assign`, `roles.manage`                                              |
| Dashboard     | `dashboard.view`                                                                          |

- **`users.reset-password` es un permiso INDEPENDIENTE**, no implícito en
  `users.update`. Restablecer la contraseña de otro usuario es una acción
  sensible que se concede por separado.
- Asignación: el `SuperAdministrador` tiene todos los permisos;
  `AdministradorCongregacion` gestiona usuarios de su congregación
  (`users.*` incluido `users.reset-password`, `roles.assign`, `dashboard.view`).

### Módulo Usuarios — reglas aprobadas

- **Email único global:** el `email` es único en toda la plataforma; la unicidad
  **no** se acota por congregación.
- **Usuario inactivo no puede iniciar sesión:** el login solo autentica usuarios
  con `estado = active` (aplicado en `LoginRequest`).
- **Un rol por usuario (en la UI):** cada usuario tiene exactamente un rol; al
  crear/editar se envía un único `role` y se aplica con `syncRoles([$role])`.
- **Último AdministradorCongregación protegido:** no se puede desactivar ni
  degradar al único `AdministradorCongregacion` activo de una congregación (debe
  quedar siempre al menos uno).
- Un usuario **no** puede cambiar su propio estado (evita autobloqueo).
- Autorización en `UserPolicy`: verifica el permiso de Spatie **y** que el
  recurso pertenezca a la misma congregación (el `SuperAdministrador` omite el
  filtro). Defensa en profundidad: middleware `permission:` (ruta) + Policy
  (acción) + Form Requests (entrada).
- **Capa actual:** backend (Policy, Form Requests, controlador de acciones,
  rutas protegidas y pruebas de autorización). **Sin vistas, tablas ni
  formularios** todavía.

### Aislamiento multi-congregación

- El aislamiento entre congregaciones se resuelve mediante **`congregation_id`**
  y **Policies/Gates**, **NO** mediante roles personalizados.
- Un usuario de una congregación **nunca** debe poder ver datos de otra.
- Aplicar un **Global Scope** de Eloquent (`CongregationScope`) para filtrar por
  `congregation_id` automáticamente, salvo para el `SuperAdministrador`.
- **Toda** consulta de tablas de negocio debe filtrarse por `congregation_id`.
- El **tenant se resuelve por subdominio desde el MVP** (middleware
  `IdentifyCongregation`), y además cada usuario mantiene su `congregation_id`.

### Validación estricta de tenant (login)

- Un usuario **solo** puede iniciar sesión en el **subdominio de su propia
  congregación**.
- Si el `congregation_id` del usuario no coincide con la congregación del
  subdominio, el login se rechaza.
- **Única excepción:** el `SuperAdministrador`, que puede acceder a cualquier
  subdominio / al área global.

---

## 4. Convenciones de idioma

- **Interfaz (texto visible):** español. Ej.: "Congregaciones", "Usuarios".
- **Código (tablas, modelos, variables, métodos, clases):** inglés. Ej.:
  `Congregation`, `User`, `AuditLog`, `CongregationScope`.
- **Excepción acordada:** los nombres de columna del dominio definidos en el
  enunciado se mantienen como se especificaron (`nombre`, `apellidos`,
  `subdominio`, `estado`). Los **valores** de los enums sí van en inglés.

---

## 5. Reportes PDF

Los reportes PDF deben generarse utilizando:

- **Laravel DomPDF** (`barryvdh/laravel-dompdf`).
- **Plantillas Blade**.

Reglas:

- **No** utilizar librerías externas de pago.
- Todos los PDFs deben poder personalizarse mediante **HTML y CSS**.
- Las plantillas Blade de PDF se ubicarán en `resources/views/pdf/`.

---

## 6. Seguridad

- Login / Logout con el sistema de autenticación de Laravel.
- Middleware de autenticación.
- Middleware de permisos (Spatie).
- Middleware de identificación/validación de tenant (`IdentifyCongregation`).
- Protección CSRF en todos los formularios.
- Hash seguro de contraseñas (bcrypt/argon2 vía `Hash`).

---

## 7. Persistencia y estados

- **No** se permite borrado físico de congregaciones. Usar **SoftDeletes**
  (`deleted_at`) para mantener el historial.
- Desactivación lógica mediante el campo `estado`.
- El campo `estado` se modela como **ENUM** (no boolean), respaldado por un
  **Enum de PHP** para tipado fuerte:
  - `congregations.estado`: `enum('active','inactive','suspended')`, por defecto
    `active`.
  - `users.estado`: `enum('active','inactive')`, por defecto `active`.

---

## 8. Auditoría

- Desde el **MVP** existe la tabla **`audit_logs`** para registrar acciones
  relevantes (creación, actualización, borrado lógico, login/logout, etc.).
- Cada registro incluye `congregation_id` (nullable para acciones globales del
  SuperAdministrador), `user_id`, el modelo afectado (morph), valores antes/después
  (JSON), IP y user agent.
- En el MVP se crea la **migración y el modelo**; el registro automático de
  eventos se irá conectando por módulo.

---

## 9. Buenas prácticas

- Seguir las convenciones estándar de Laravel (estructura de carpetas, naming).
- Validación mediante Form Requests.
- Lógica de autorización en Policies/Gates.
- Migraciones, modelos Eloquent con relaciones correctas y seeders iniciales.
- Mantener el código limpio, organizado y listo para subir a GitHub.

---

## 10. Resumen de decisiones aprobadas

1. **Congregaciones:** sin borrado físico; **SoftDeletes** (`deleted_at`);
   desactivación lógica; FK `users.congregation_id` con **ON DELETE RESTRICT**.
2. **Estado congregación:** ENUM `active | inactive | suspended` (default `active`).
   **Estado usuario:** ENUM `active | inactive` (default `active`).
3. **Multi-congregación:** tenant por **subdominio** desde el MVP +
   `congregation_id` por usuario + **Global Scope** que filtra toda consulta.
4. **Validación estricta de tenant:** el usuario solo inicia sesión en el
   subdominio de su congregación; **SuperAdministrador** es la única excepción.
5. **RBAC:** spatie/laravel-permission, **sin Teams**; 1 usuario → 1 congregación.
   **Un rol por usuario** en la UI. `users.reset-password` es un permiso
   **independiente** de `users.update`.
6. **Módulo Usuarios:** email **único global**; usuario **inactivo no puede
   iniciar sesión**; **último AdministradorCongregación protegido** (no se puede
   desactivar/degradar si es el único activo de su congregación).
7. **Auditoría:** tabla `audit_logs` creada desde el MVP.
8. **PDF:** DomPDF + Blade (HTML/CSS), sin librerías de pago.
9. **UI:** **Bootstrap 5** + tipografía **Google Sans Flex**.

---

## 11. Estado actual

Rama `feature/estructura-laravel` (mergeada): **estructura base**
(Laravel 12, MySQL, Spatie, DomPDF, migraciones iniciales, modelos, seeders,
login/logout y dashboard básico).

Rama `feature/usuarios`: **capa backend base del módulo Usuarios**:
`UserPolicy`, permiso `users.reset-password`, Form Requests
(`StoreUserRequest`, `UpdateUserRequest`, `ResetUserPasswordRequest`),
`UserController` (acciones de escritura) con rutas protegidas y pruebas de
autorización. **Sin vistas, tablas ni formularios** (se construyen después).

Rama `feature/usuarios-listado`: **listado de usuarios (UI)**:
`UserController@index` con **búsqueda** (nombre/apellidos/email), **filtros**
(estado y rol), **paginación Bootstrap 5** y aislamiento por congregación
(`viewAny` de la `UserPolicy`). **Aún sin formularios de alta/edición.**

> **Nota de UI (Bootstrap 5):** el andamiaje inicial usaba Tailwind por CDN. Para
> cumplir la decisión aprobada (Bootstrap 5 + Google Sans Flex), el panel se
> migró a **Bootstrap 5**: se actualizaron el layout compartido
> (`layouts/app.blade.php`) y las vistas que dependen de él (`dashboard`,
> `placeholder`, `auth/login`), y se activó `Paginator::useBootstrapFive()`.

Rama `feature/usuarios-crud`: **alta/edición, cambio de estado, asignación de
rol y auditoría**:
- Formularios de **crear** (`users/create`) y **editar** (`users/edit`) con
  partial compartido `users/_form` (un único rol por usuario; selector de
  congregación solo para el SuperAdministrador).
- Acciones por fila en el listado: **Editar** y **Activar/Desactivar** (con las
  comprobaciones de la `UserPolicy`).
- **Auditoría (`audit_logs`)**: cada acción de escritura registra un evento
  (`user.created`, `user.updated`, `user.status_changed`, `user.password_reset`)
  mediante `App\Support\AuditLogger`, capturando autor, congregación, IP y
  user-agent. La edición guarda **solo los campos modificados** y **nunca**
  se registran contraseñas.

> **Auditoría — escritura por módulo:** la grabación de eventos es **explícita**
> en el controlador (no por observers globales), conforme a lo previsto en el
> modelo `AuditLog`.
