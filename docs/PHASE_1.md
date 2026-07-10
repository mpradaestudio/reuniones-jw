# PHASE_1.md

# Reuniones JW

## Fase 1 – Alineación del Dominio

**Versión:** 1.0  
**Estado:** Pendiente

---

# Objetivo

La Fase 1 tiene como objetivo alinear el código existente con el dominio aprobado durante el Sprint 0.

No se desarrollarán nuevas funcionalidades del negocio hasta completar esta alineación.

Se aprovechará toda la infraestructura existente del proyecto y se eliminarán las discrepancias detectadas en la auditoría técnica.

---

# Objetivos Específicos

- Completar el módulo Personas.
- Rediseñar el modelo de Privilegios.
- Implementar Perfil Local.
- Eliminar inconsistencias entre documentación y código.
- Preparar el dominio para el módulo de Programas.

---

# Estado General

| Historia | Estado |
|----------|--------|
| PH1-001 | Pendiente |
| PH1-002 | Pendiente |
| PH1-003 | Pendiente |
| PH1-004 | Pendiente |
| PH1-005 | Pendiente |
| PH1-006 | Pendiente |

---

# PH1-001

## Completar módulo Personas

### Objetivo

Completar el CRUD reutilizando toda la infraestructura existente.

### Incluye

- Listado.
- Crear.
- Editar.
- Eliminar.
- Activar.
- Desactivar.
- Búsquedas.
- Filtros.
- Bootstrap 5.

### No incluye

- Perfil Local.
- Privilegios múltiples.
- Programas.

### Dependencias

Ninguna.

### Riesgos

No modificar la estructura de la base de datos.

No renombrar modelos.

### Estado

Pendiente.

### Resultado esperado

CRUD completamente funcional.

---

# PH1-002

## Diseñar el catálogo de Privilegios

### Objetivo

Sustituir el modelo basado en ENUM por un catálogo administrable.

### Incluye

- Diseño.
- Modelo.
- Relaciones.
- Migraciones.

### No incluye

Implementación funcional.

### Dependencias

PH1-001

### Riesgos

Mantener compatibilidad con el código existente.

### Estado

Pendiente.

---

# PH1-003

## Refactorizar Privilegios

### Objetivo

Implementar la nueva estructura aprobada.

### Incluye

- Tabla Privilegios.
- Tabla pivote.
- Relación muchos a muchos.

### Dependencias

PH1-002

### Riesgos

Migración de datos.

### Estado

Pendiente.

---

# PH1-004

## Implementar Perfil Local

### Objetivo

Crear el nuevo módulo Perfil Local.

### Incluye

- Modelo.
- CRUD.
- Relaciones.
- Policies.

### Dependencias

PH1-003

### Estado

Pendiente.

---

# PH1-005

## Integrar Personas

### Objetivo

Integrar Personas con:

- Perfil Local.
- Privilegios.
- Congregación.

### Dependencias

PH1-004

### Estado

Pendiente.

---

# PH1-006

## Validación Final

### Objetivo

Validar que el dominio quedó alineado con la documentación.

### Incluye

- Revisión arquitectónica.
- Revisión funcional.
- Auditoría.
- Tests.

### Dependencias

Todas las anteriores.

### Estado

Pendiente.

---

# Riesgos

- Introducir deuda técnica.
- Romper compatibilidad.
- Modificar reglas del negocio.
- Duplicar entidades.

---

# Definition of Ready

Una historia podrá comenzar cuando:

- Exista documentación.
- Exista dominio definido.
- Exista aprobación de arquitectura.

---

# Definition of Done

Una historia estará terminada cuando:

- Cumpla PRODUCT.md.
- Cumpla DOMAIN_MODEL.md.
- Cumpla BUSINESS_RULES.md.
- Cumpla AGENT_RULES.md.
- Pase pruebas.
- Actualice CHANGELOG_AI.md.
- Pase revisión arquitectónica.

---

# Resultado esperado

Al finalizar la Fase 1 el proyecto deberá contar con un dominio completamente alineado con la documentación aprobada durante el Sprint 0.

A partir de ese momento podrá comenzar el desarrollo del módulo central de Reuniones.
