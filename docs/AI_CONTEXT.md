# AI Context

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
