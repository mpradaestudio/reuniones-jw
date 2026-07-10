# BACKLOG.md

# Reuniones JW
## Product Backlog

**Versión:** 1.0  
**Estado:** Aprobado

---

# Objetivo

Este documento contiene el Product Backlog oficial del proyecto.

El Backlog está organizado por:

- Épicas
- Capacidades
- Historias de Usuario
- Prioridades
- Dependencias

Las historias deberán implementarse siguiendo el orden establecido.

---

# Prioridades

| Prioridad | Significado |
|------------|------------|
| P0 | Imprescindible para el funcionamiento del sistema |
| P1 | Muy importante |
| P2 | Importante |
| P3 | Futuro |

---

# EPIC 01
# Plataforma

Objetivo:

Construir el núcleo técnico de la plataforma.

---

## Capacidad

Autenticación

Historias

- Login
- Logout
- Recuperar contraseña
- Cambiar contraseña

Prioridad

P0

---

## Capacidad

Usuarios

Historias

- Crear Usuario
- Editar Usuario
- Desactivar Usuario
- Reactivar Usuario

Prioridad

P0

---

## Capacidad

Roles

Historias

- Crear Rol
- Editar Rol
- Eliminar Rol
- Asignar Permisos

Prioridad

P0

---

## Capacidad

Multitenancy

Historias

- Crear Congregación
- Asociar Usuario
- Cambiar Congregación
- Aislamiento de datos

Prioridad

P0

---

## Capacidad

Auditoría

Historias

- Registrar cambios
- Consultar historial

Prioridad

P1

---

# EPIC 02
# Congregación

Objetivo

Administrar toda la información local.

---

## Capacidad

Configuración

Historias

- Editar datos generales
- Configurar horarios
- Configurar días de reunión
- Configurar logo

Prioridad

P0

---

## Capacidad

Personas

Historias

- Registrar Persona
- Editar Persona
- Desactivar Persona
- Buscar Persona

Prioridad

P0

---

## Capacidad

Perfiles Locales

Historias

- Crear Perfil
- Editar Perfil
- Asignar Perfil
- Quitar Perfil

Prioridad

P0

---

## Capacidad

Privilegios

Historias

- Crear Privilegio
- Editar Privilegio
- Asignar Privilegio
- Quitar Privilegio

Prioridad

P0

---

## Capacidad

Grupos de Servicio

Historias

- Crear Grupo
- Editar Grupo
- Asignar Personas

Prioridad

P1

---

## Capacidad

Oradores Visitantes

Historias

- Registrar Orador
- Editar Orador
- Historial de visitas

Prioridad

P2

---

## Capacidad

Eventos Especiales

Historias

- Crear Evento
- Editar Evento
- Cancelar reunión
- Reemplazar Programa

Prioridad

P1

---

# EPIC 03
# Reuniones

Objetivo

Administrar completamente la programación de reuniones.

---

## Capacidad

Motor de Importación

Historias

- Importar Programa
- Validar Programa
- Crear Programa Oficial

Prioridad

P0

---

## Capacidad

Programa Local

Historias

- Crear copia local
- Editar Programa
- Restablecer contenido

Prioridad

P0

---

## Capacidad

Administrar Programa

Historias

- Editar Secciones
- Editar Partes
- Cambiar Canciones
- Agregar Parte
- Eliminar Parte

Prioridad

P0

---

## Capacidad

Asignaciones

Historias

- Crear Asignación
- Editar Asignación
- Asignar Persona
- Cambiar Asignado
- Quitar Asignado

Prioridad

P0

---

## Capacidad

Exportación PDF

Historias

- Generar PDF
- Vista previa
- Descargar PDF

Prioridad

P0

---

# EPIC 04
# Ministerio

Objetivo

Administrar la predicación pública mediante exhibidores.

---

## Capacidad

Lugares

Historias

- Crear Lugar
- Editar Lugar
- Desactivar Lugar

Prioridad

P1

---

## Capacidad

Horarios

Historias

- Crear Horario
- Editar Horario
- Eliminar Horario

Prioridad

P1

---

## Capacidad

Turnos

Historias

- Crear Turno
- Editar Turno
- Asignar Personas

Prioridad

P1

---

# EPIC 05
# Reportes

Objetivo

Generar información consolidada.

---

## Capacidad

Reportes

Historias

- Programas realizados
- Personas asignadas
- Historial de asignaciones

Prioridad

P2

---

# EPIC 06
# Integraciones

Objetivo

Conectar la plataforma con servicios externos.

---

## Capacidad

WhatsApp

Prioridad

P3

---

## Capacidad

Backups

Prioridad

P3

---

## Capacidad

Calendarios

Prioridad

P3

---

# MVP

El MVP estará completo cuando existan las siguientes capacidades:

✅ Login

✅ Usuarios

✅ Roles

✅ Congregaciones

✅ Personas

✅ Perfiles Locales

✅ Privilegios

✅ Configuración

✅ Importación

✅ Programa Oficial

✅ Programa Local

✅ Administración del Programa

✅ Asignaciones

✅ Exportación PDF

✅ Exhibidores

---

# Dependencias

Plataforma

↓

Congregación

↓

Reuniones

↓

Ministerio

↓

Reportes

↓

Integraciones

---

# Definition of Ready

Una Historia de Usuario estará lista para desarrollarse cuando:

- Pertenezca a una Capacidad.
- Tenga una prioridad.
- Existan reglas de negocio asociadas.
- Exista una definición clara del dominio.

---

# Definition of Done

Una Historia se considerará terminada cuando:

- Cumpla los criterios de aceptación.
- Respete el DOMAIN_MODEL.
- Respete BUSINESS_RULES.
- Tenga pruebas.
- Pase revisión de código.
- Actualice CHANGELOG_AI.md.
- Sea aprobada mediante Pull Request.

---

# Regla General

Ninguna Historia podrá implementarse si contradice:

- VISION.md
- DOMAIN_MAP.md
- BUSINESS_RULES.md
- DOMAIN_MODEL.md
- PRODUCT.md

Estos documentos constituyen la fuente oficial del proyecto.
