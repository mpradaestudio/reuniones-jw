# AI Context

Este documento es la guía principal para cualquier IA que trabaje en este repositorio.

Antes de modificar el código debes leer este archivo completo.

Después consulta, cuando sea necesario:

- ARCHITECTURE.md
- CONVENTIONS.md
- DATABASE.md
- SECURITY.md
- ROADMAP.md
- DECISIONS.md

Si existe conflicto entre la conversación y la documentación, solicita aclaración antes de modificar el proyecto.

No modifiques la arquitectura sin justificar el cambio.

No elimines funcionalidades existentes.

No cambies Bootstrap 5 por otro framework.

Ejecuta pruebas cuando modifiques funcionalidades existentes.

## Proyecto

Sistema web para la gestión de congregaciones.

Permite administrar:

- usuarios
- congregaciones
- privilegios
- reuniones
- asignaciones
- discursos
- horarios
- reportes

---

# Stack

- Laravel 12
- PHP 8.3
- Bootstrap 5
- MySQL
- XAMPP
- Blade
- Vite

---

# Convenciones

Siempre usar Bootstrap 5.

No usar Tailwind.

No instalar Livewire salvo autorización.

No cambiar la arquitectura MVC.

Seguir PSR-12.

---

# Git

Trabajar mediante ramas feature/*.

No hacer cambios directamente en main.

---

# Arquitectura

Controladores delgados.

La lógica de negocio debe ir en Services.

Modelos únicamente para acceso a datos.

Policies para autorización.

Migraciones incrementales.

---

# Base de datos

MySQL.

Migraciones mediante Artisan.

No modificar tablas manualmente.

---

# Objetivos

Crear un sistema multi-congregación.

Gestionar usuarios.

Gestionar reuniones.

Gestionar asignaciones.

Generar reportes.

Mantener el código escalable.

---

# Reglas para IA

Antes de modificar archivos:

- entender el contexto
- buscar reutilización
- evitar duplicación
- respetar la arquitectura existente

No eliminar funcionalidades existentes sin autorización.

Siempre explicar cambios grandes antes de aplicarlos.
