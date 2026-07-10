# IMPLEMENTATION_AUDIT.md

# Reuniones JW

## Auditoría Técnica de Implementación

**Versión:** 1.0

**Estado:** Completado

---

# Objetivo

Este documento registra el estado real de implementación del proyecto.

Su propósito es comparar la documentación funcional con el código existente para determinar qué funcionalidades ya están implementadas, cuáles están parcialmente desarrolladas y cuáles aún no existen.

Este documento deberá mantenerse actualizado durante todo el ciclo de vida del proyecto.

---

# Estados

| Estado | Significado |
|----------|------------|
| ✅ | Implementado |
| 🟡 | Parcialmente implementado |
| ❌ | No implementado |
| 🔍 | Pendiente de revisión |

---

# Plataforma

| Módulo | Estado | Observaciones |
|----------|--------|---------------|
| Laravel 12 | ✅ | Se utiliza la versión 12.0.x del framework (indicada en `composer.json`). Estructura estándar MVC, uso del contenedor de servicios y configuración adecuada. |
| Bootstrap 5 | ✅ | Configurado y aplicado como único framework CSS en las vistas Blade (`layouts/app`, `dashboard`, `users/`, `roles/`, `audit/`). Se activó la paginación con Bootstrap 5 (`Paginator::useBootstrapFive()`) en `AppServiceProvider`. |
| Autenticación | 🟡 | Inicio y cierre de sesión implementados con validación y aislamiento estricto por tenant (los usuarios solo ingresan en el subdominio de su propia congregación). Falta la recuperación y el cambio de contraseña propia (historias P0 del backlog). |
| Usuarios | ✅ | CRUD completo funcional y verificado. Aislamiento por congregación a nivel de controlador y policy. Reglas de negocio críticas aplicadas (protección del último administrador activo, autobloqueo de estado). Vistas en Bootstrap 5 completas. |
| Roles | ✅ | CRUD funcional para roles personalizados y roles de sistema protegidos. Integrado con vistas Bootstrap 5 de visualización, creación, edición, duplicado y un asistente de reasignación de usuarios al eliminar. |
| Permisos | ✅ | Integración completa con Spatie Laravel-Permission (sin Teams). Permisos definidos en el código (`RolePermissionSeeder`) y asignables en la interfaz. Permiso `users.reset-password` independiente de `users.update` según lo acordado. |
| Multitenancy | ✅ | Aislamiento robusto por columna discriminadora `congregation_id`. Resolución por subdominio vía middleware `IdentifyCongregation` y singleton `Tenant`. Aislamiento automático en Eloquent implementado vía trait `BelongsToCongregation` y Global Scope `CongregationScope`. Congregaciones usan `SoftDeletes` y desactivación lógica por estado. |
| Auditoría | ✅ | Base de datos, modelo `AuditLog`, helper `AuditLogger` y vistas Bootstrap 5 para listado y detalle. Se registran eventos de Usuarios, Roles y Publicadores de forma explícita en los controladores, capturando datos antes/después (en formato JSON), autor, IP y user-agent. Aislamiento por congregación funcionando. |

---

# Congregación

| Módulo | Estado | Observaciones |
|----------|--------|---------------|
| Congregaciones | 🟡 | El modelo, migración con índices y SoftDeletes, factory y seeders están implementados. Sin embargo, no existe un controlador ni vistas para su gestión (CRUD). La ruta apunta a una pantalla temporal (placeholder). |
| Configuración | ❌ | No implementado. La ruta apunta a un placeholder de configuración general. No existen tablas, modelos, controladores ni vistas. |
| Personas | 🟡 | Implementado bajo el nombre de **Publicadores** (Publishers). Existe el modelo `Publisher`, migración con índices, controladores con acciones de escritura (store, update, toggleStatus, delete) y Policies. No obstante, **faltan por completo las vistas** (no existe la carpeta `resources/views/publishers` y el método `index` del controlador devuelve una vista que no existe). Existe discrepancia terminológica entre documentación ("Personas") y código ("Publishers"). |
| Perfil Local | ❌ | No implementado. No existen tablas, modelos, controladores ni vistas. |
| Privilegios | 🟡 | Existe una columna `privilegio` de tipo ENUM en la tabla `publishers` y un enum `PublisherPrivilege`. Sin embargo, esto **no cumple las reglas de dominio BR-103** ("Un Publicador puede tener múltiples Privilegios") ni **BR-104** ("Los Privilegios son administrables mediante un catálogo"), ya que se limita a un único valor fijo por publicador y no es editable a través de un catálogo. |
| Grupos de Servicio | ❌ | No implementado. Sin base de datos, modelos ni vistas. |
| Eventos Especiales | ❌ | No implementado. Sin base de datos, modelos ni vistas. |
| Oradores Visitantes | ❌ | No implementado. Sin base de datos, modelos ni vistas. |

---

# Reuniones

| Módulo | Estado | Observaciones |
|----------|--------|---------------|
| Motor de Importación | ❌ | No implementado. |
| Programa Oficial | ❌ | No implementado. |
| Programa Local | ❌ | No implementado. |
| Secciones | ❌ | No implementado. |
| Partes | ❌ | No implementado. |
| Asignaciones | ❌ | No implementado. |
| Exportación PDF | ❌ | No implementado. Se instaló el paquete `barryvdh/laravel-dompdf` y se creó una vista de prueba `pdf.example`, pero no hay ninguna integración, lógica o rutas para generar PDFs de los programas (ya que la funcionalidad de programas no existe). |

---

# Ministerio

| Módulo | Estado | Observaciones |
|----------|--------|---------------|
| Exhibidores | ❌ | No implementado. |
| Lugares | ❌ | No implementado. |
| Horarios | ❌ | No implementado. |
| Turnos | ❌ | No implementado. |
| Asignados | ❌ | No implementado. |

---

# Infraestructura

| Elemento | Estado | Observaciones |
|-----------|--------|---------------|
| PHPUnit | ✅ | Configurado y con 60 pruebas de feature implementadas (198 aserciones) con alta cobertura en Usuarios, Roles, Auditoría y Publicadores. Sin embargo, no se pueden ejecutar las pruebas localmente en el CLI por defecto porque el driver de PHP CLI no tiene habilitada la extensión SQLite (`pdo_sqlite` missing driver error). En GitHub Actions funciona correctamente. |
| GitHub Actions | ✅ | Flujo de CI completo configurado en `.github/workflows/ci.yml`. Ejecuta pruebas en PHP 8.4, instala dependencias Composer, genera claves y corre análisis de estilo mediante Laravel Pint. |
| Policies | ✅ | Implementadas `UserPolicy`, `RolePolicy`, `AuditLogPolicy` y `PublisherPolicy` con validación estricta de tenant y lógica de negocio. |
| Form Requests | ✅ | Validaciones de entrada centralizadas e implementadas para Usuarios, Roles y Publicadores. |
| Seeders | ✅ | Seeders funcionales creados en `database/seeders` (`DatabaseSeeder`, `CongregationSeeder`, `RolePermissionSeeder`, `UserSeeder`) para cargar la estructura y datos de prueba. |
| Factories | ✅ | Factories implementados para `User`, `Congregation`, `Publisher` y `AuditLog` listos para usar en testing. |
| Migrations | ✅ | Estructura de base de datos migrada para Congregaciones, Usuarios, Roles (Spatie), Auditoría y Publicadores. |

---

# Deuda Técnica

1. **Desalineación del Dominio en Privilegios**:
   - La regla **BR-103** ("Un Publicador puede tener múltiples Privilegios") y la regla **BR-104** ("Los Privilegios son administrables mediante un catálogo") no se respetan en el código actual. Se implementaron como una columna `privilegio` de tipo ENUM en la tabla `publishers` (`'publicador', 'siervo_ministerial', 'anciano'`). Esto limita al publicador a un solo privilegio y acopla las opciones en la estructura física de la base de datos sin catálogo administrable.
2. **Discrepancia de Nombres (Personas vs Publicadores)**:
   - Los documentos funcionales de análisis (`PRODUCT.md`, `DOMAIN_MAP.md`, `DOMAIN_MODEL.md`) hacen referencia a la entidad **Personas**, pero en el código se implementó como la tabla `publishers` y el modelo `Publisher`.
3. **Módulo de Publicadores incompleto**:
   - Aunque la base de datos, el controlador (`PublisherController`), las Form Requests, las Policies y los tests están creados para `Publisher`, el módulo carece de vistas Blade (`publishers/index`, `publishers/create`, `publishers/edit`). Al invocar las rutas de publicadores, el sistema fallará buscando las vistas.
4. **Acoplamiento de Auditoría a Controladores**:
   - `AuditLogger::record` se invoca explícitamente desde cada método del controlador en lugar de utilizar observers de Eloquent u events del sistema. Esto aumenta la deuda técnica ya que el desarrollador debe recordar añadir la llamada de auditoría manualmente en cada nueva acción.

---

# Riesgos

1. **Falta de Driver de base de datos en Entorno CLI Local (SQLite)**:
   - El entorno de ejecución local en Windows carece del driver `pdo_sqlite` habilitado para PHP CLI, lo cual impide ejecutar la suite de pruebas mediante `php artisan test` de forma local. Las pruebas fallan en su totalidad en el entorno local del usuario por problemas del controlador SQLite, aunque pasen en CI.
2. **Incompatibilidad de PHP en Entornos de Desarrollo**:
   - `composer.json` requiere `php: ^8.2` pero `composer.lock` se resolvió utilizando PHP 8.4 (con paquetes de Symfony 8.1.x que requieren >=8.4.1). El flujo de GitHub Actions también usa PHP 8.4. Si el servidor local corre PHP 8.2 u 8.3, podrían existir problemas de incompatibilidad de dependencias al intentar reinstalar.
3. **Falta de Aislamiento de Spatie Permission (Multitenancy)**:
   - Se decidió no habilitar el modo `Teams` de Spatie Permission en el MVP. Dado que los roles y permisos son globales, no hay aislamiento por congregación a nivel de la tabla `roles` o `permissions`. Si se crearan roles personalizados, serían compartidos/visibles por todas las congregaciones en la base de datos, aunque el aislamiento del controlador y el `AuditLogPolicy`/`UserPolicy` limiten las asignaciones.

---

# Recomendaciones

1. **Refactorizar Privilegios a Relación y Catálogo**:
   - Migrar la columna `privilegio` de `publishers` hacia una relación de muchos a muchos con una tabla `privileges` configurable, de modo que se cumplan las reglas **BR-103** y **BR-104**.
2. **Implementar las Vistas del Módulo de Publicadores**:
   - Crear el directorio `resources/views/publishers` e implementar los listados, formularios y lógica Bootstrap 5 para el CRUD de Publicadores para completar el alcance básico del MVP.
3. **Habilitar Extensiones PHP Locales**:
   - Habilitar `extension=pdo_sqlite` y `extension=sqlite3` en el archivo `php.ini` del entorno local de desarrollo para permitir la ejecución local de la suite de pruebas unitarias y de feature.
4. **Renombrar/Alinear Entidad Personas**:
   - Evaluar si es viable cambiar el nombre de la entidad `Publisher` / `publishers` en la base de datos a `Persona` / `people` para alinearlo completamente con el vocabulario funcional, o en su defecto, ajustar la documentación para que refleje el término de desarrollo "Publicadores".
5. **Implementar Configuración de Congregaciones**:
   - Crear la tabla de `congregation_settings` y su modelo correspondiente para eliminar el placeholder de la sección de Configuración y permitir definir los días y horas de reunión por congregación.

---

# Próxima revisión

La siguiente auditoría deberá realizarse una vez finalice la primera fase de implementación.
