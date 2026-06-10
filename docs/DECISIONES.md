# Registro de decisiones de arquitectura (ADR) — reuniones-jw

Este documento registra las decisiones de arquitectura **cerradas** que guían la
implementación del MVP. Formato ligero tipo ADR.

---

## ADR-001 — Borrado y persistencia de congregaciones

**Estado:** Aceptada

**Contexto:** Se necesita conservar el historial y evitar pérdidas de información
al "eliminar" una congregación.

**Decisión:**
- No se permite **borrado físico** de congregaciones.
- Se usa **SoftDeletes** de Laravel (columna `deleted_at`) para mantener el
  historial.
- La baja operativa se hace por **desactivación lógica** con `estado = 'inactiva'`.
- La FK `users.congregation_id → congregations.id` usa **ON DELETE RESTRICT**.

**Consecuencias:**
- El modelo `Congregation` usa el trait `SoftDeletes`.
- Las consultas excluyen por defecto los registros con `deleted_at`.
- Para administración global se podrá usar `withTrashed()` (solo SuperAdmin).

---

## ADR-002 — Estrategia multi-congregación (tenant por subdominio)

**Estado:** Aceptada

**Contexto:** Cada congregación debe estar aislada; un usuario nunca debe ver
datos de otra congregación.

**Decisión:**
- El **tenant se resuelve por subdominio desde el MVP**
  (`congregacion-a.midominio.com`) mediante un middleware `IdentifyCongregation`.
- Cada usuario mantiene su `congregation_id`. Tras el login se valida que coincida
  con la congregación del subdominio (excepto `SuperAdministrador`).
- **Toda** consulta de tablas de negocio se filtra por `congregation_id` mediante
  un **Global Scope** (`CongregationScope`) + trait `BelongsToCongregation`.
- El Global Scope se desactiva para el `SuperAdministrador`.

**Consecuencias:**
- Toda tabla de negocio (presente/futura) incluye `congregation_id`.
- Defensa en profundidad: Global Scope (consulta) + Policies (acción) +
  Form Requests (entrada).
- Configuración de Apache/hosts local (XAMPP) con `ServerAlias *.midominio.local`
  para desarrollo de subdominios.

---

## ADR-003 — RBAC con Spatie sin modo Teams

**Estado:** Aceptada

**Contexto:** Se requiere RBAC extensible sin construir un sistema propio.

**Decisión:**
- Usar **spatie/laravel-permission** con sus tablas y convenciones estándar.
- **No** activar el **modo Teams** por ahora.
- Cada usuario pertenece a **una sola** congregación.
- El aislamiento NO depende de roles, sino de `congregation_id` + Policies.

**Consecuencias:**
- Roles iniciales: `SuperAdministrador`, `AdministradorCongregacion`, `Usuario`.
- Se pueden añadir roles/permisos vía seeder o UI sin cambiar la arquitectura.
- Si en el futuro se necesita el mismo rol con alcance por congregación a nivel
  de Spatie, se reevaluará activar Teams (`team_id = congregation_id`).

---

## ADR-004 — Campo `estado` como ENUM

**Estado:** Aceptada

**Contexto:** El estado podría necesitar más valores en el futuro (p. ej.
`suspendida`, `pendiente`).

**Decisión:**
- Modelar `estado` como **ENUM** en lugar de boolean.
  - `congregations.estado`: `enum('activa','inactiva')`, default `activa`.
  - `users.estado`: `enum('activo','inactivo')`, default `activo`.
- En Laravel se respaldará con **Enums de PHP** (cast `enum`) para tipado fuerte.

**Consecuencias:**
- Mayor flexibilidad para añadir estados sin migrar de tipo de columna.
- Validación de valores permitidos centralizada en el Enum de PHP.

---

## Resumen

| ADR | Tema                         | Decisión clave                                            |
|-----|------------------------------|-----------------------------------------------------------|
| 001 | Persistencia congregaciones  | SoftDeletes + desactivación lógica, sin borrado físico    |
| 002 | Multi-congregación           | Tenant por subdominio (MVP) + Global Scope por `congregation_id` |
| 003 | RBAC                         | Spatie sin Teams; 1 usuario → 1 congregación              |
| 004 | Estado                       | ENUM en lugar de boolean                                  |
