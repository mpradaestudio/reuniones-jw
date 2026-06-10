# CLAUDE.md — Guía del proyecto reuniones-jw

Este archivo define las convenciones, decisiones de arquitectura y restricciones
que cualquier asistente de IA (o desarrollador) debe respetar al trabajar en este
proyecto.

---

## 1. Descripción general

Aplicación web **multi-congregación** para la gestión de congregaciones:
usuarios, roles, permisos, horarios, programación semanal, asignaciones y
discursos. Debe estar preparada para crecer con nuevos módulos sin cambiar la
arquitectura base.

---

## 2. Stack tecnológico obligatorio

| Capa              | Tecnología            |
|-------------------|-----------------------|
| Backend           | PHP 8.2+              |
| Framework         | Laravel 12            |
| Base de datos     | MySQL                 |
| Servidor web      | Apache                |
| Entorno local     | XAMPP                 |
| Control de versiones | GitHub (repo: `reuniones-jw`) |

---

## 3. Roles y permisos

- Usar **spatie/laravel-permission**. **NO** implementar un sistema RBAC propio.
- Utilizar las tablas y convenciones estándar de Spatie.
- Roles iniciales: `SuperAdministrador`, `AdministradorCongregacion`, `Usuario`.
- El sistema debe permitir agregar nuevos roles y permisos en el futuro sin
  modificar la arquitectura.

### Aislamiento multi-congregación

- El aislamiento entre congregaciones se resuelve mediante **`congregation_id`**
  y **Policies/Gates**, **NO** mediante roles personalizados.
- Un usuario de una congregación **nunca** debe poder ver datos de otra.
- Aplicar un **Global Scope** de Eloquent para filtrar por `congregation_id`
  automáticamente, salvo para el `SuperAdministrador`.
- **Toda** consulta de tablas de negocio debe filtrarse por `congregation_id`.
- El **tenant se resuelve por subdominio desde el MVP** (middleware
  `IdentifyCongregation`), y además cada usuario mantiene su `congregation_id`.
- **Spatie Teams**: NO se activa por ahora. Cada usuario pertenece a una sola
  congregación.

---

## 4. Convenciones de idioma

- **Interfaz (texto visible):** español. Ej.: "Congregaciones", "Usuarios".
- **Código (tablas, modelos, variables, métodos):** inglés. Ej.: `congregations`,
  `users`, `Congregation`, `roleableUsers()`.

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
- Protección CSRF en todos los formularios.
- Hash seguro de contraseñas (bcrypt/argon2 vía `Hash`).

---

## 7. Persistencia y estados

- **No** se permite borrado físico de congregaciones. Usar **SoftDeletes**
  (`deleted_at`) para mantener el historial.
- Desactivación lógica mediante el campo `estado`.
- El campo `estado` se modela como **ENUM** (no boolean) para permitir futuras
  ampliaciones de estados.
  - `congregations.estado`: `enum('activa','inactiva')`, por defecto `activa`.
  - `users.estado`: `enum('activo','inactivo')`, por defecto `activo`.

## 8. Buenas prácticas

- Seguir las convenciones estándar de Laravel (estructura de carpetas, naming).
- Validación mediante Form Requests.
- Lógica de autorización en Policies/Gates.
- Migraciones, modelos Eloquent con relaciones correctas y seeders iniciales.
- Mantener el código limpio, organizado y listo para subir a GitHub.

---

## 9. Estado actual

Fase de **diseño**. Aún **no** se genera código de aplicación. El primer
entregable es la documentación de análisis (ver `docs/ANALISIS.md`).
