# Convenciones del Proyecto

## Framework CSS

Siempre utilizar Bootstrap 5.

No utilizar:

- Tailwind
- Bulma
- Foundation

---

## Código PHP

Seguir PSR-12.

---

## Controllers

Los controllers solo coordinan.

No deben contener lógica compleja.

---

## Policies

Toda operación CRUD debe pasar por una Policy.

---

## Validaciones

Toda validación debe implementarse mediante Form Requests.

---

## Base de datos

Nunca modificar tablas manualmente.

Siempre crear migraciones.

---

## Git

Trabajar únicamente mediante ramas feature/*.

Ejemplos:

feature/users

feature/meetings

feature/schedules

---

## Commits

Mensajes claros.

Ejemplo:

Agregar módulo de publicadores

Corregir validación de usuarios

Implementar auditoría
