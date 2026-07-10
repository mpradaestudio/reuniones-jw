# PRODUCT.md

# Reuniones JW
## Product Requirements Document (PRD)

**Versión:** 1.0  
**Estado:** Aprobado  
**Proyecto:** Reuniones JW

---

# 1. Descripción

Reuniones JW es una plataforma SaaS diseñada para administrar de forma integral las actividades de una congregación de los Testigos de Jehová.

El sistema centraliza la información relacionada con las personas, la programación de reuniones, las asignaciones, los programas oficiales, los eventos especiales y otras actividades de la congregación, ofreciendo una solución moderna, intuitiva y segura.

Su propósito no es reemplazar el contenido oficial, sino facilitar la administración local utilizando dicho contenido como punto de partida.

---

# 2. Objetivo del Producto

Reducir el tiempo, el esfuerzo y los errores asociados a la preparación y administración de las reuniones de una congregación mediante un sistema unificado que permita organizar toda la información desde un único lugar.

---

# 3. Objetivo del MVP

Permitir administrar completamente la programación de reuniones de una congregación.

Al finalizar el MVP una congregación debe poder utilizar el sistema durante una semana completa sin depender de herramientas externas.

---

# 4. Usuarios

## SuperAdministrador

Responsable de administrar toda la plataforma.

Puede:

- Crear Congregaciones.
- Administrar Usuarios.
- Administrar Roles.
- Configurar la plataforma.
- Acceder a todas las Congregaciones.

---

## Usuario de Congregación

Usuario con acceso únicamente a una Congregación.

Sus permisos dependerán del Rol asignado dentro del sistema.

Ejemplos:

- Administrador de Congregación
- Consulta

---

## Persona

Representa cualquier individuo registrado dentro de una Congregación.

Una Persona puede existir sin tener acceso al sistema.

Solo algunas Personas tendrán un Usuario asociado.

---

# 5. Filosofía del Producto

La tecnología debe adaptarse al funcionamiento real de la congregación.

Nunca la congregación deberá adaptarse al software.

El sistema debe hablar el mismo lenguaje utilizado por los usuarios.

---

# 6. Principios

- Simplicidad antes que complejidad.
- Modularidad.
- Escalabilidad.
- Seguridad por diseño.
- Bajo mantenimiento.
- Automatización cuando sea posible.
- Configuración antes que programación.
- Preservar siempre la referencia oficial del programa.
- Facilitar la adaptación local.
- Mantener independencia total entre Congregaciones.

---

# 7. Capacidades del Producto

## Plataforma

- Autenticación.
- Administración de Usuarios.
- Administración de Roles.
- Administración de Permisos.
- Auditoría.
- Configuración Global.
- Multitenancy.

---

## Congregación

- Administrar Congregación.
- Configuración de Congregación.
- Administrar Personas.
- Administrar Perfiles Locales.
- Administrar Privilegios.
- Administrar Grupos de Servicio.
- Administrar Eventos Especiales.
- Administrar Oradores Visitantes.

---

## Reuniones

- Importar Programa Oficial.
- Crear Programa Local.
- Administrar Programa.
- Administrar Eventos.
- Administrar Partes del Programa.
- Administrar Asignaciones.
- Exportar Programa a PDF.

---

## Ministerio

- Administrar Exhibidores.
- Administrar Lugares.
- Administrar Horarios.
- Administrar Turnos.
- Administrar Asignados.

---

# 8. Flujo General

## Configuración inicial

Crear Congregación

↓

Registrar Personas

↓

Asignar Perfiles Locales

↓

Asignar Privilegios

↓

Crear Grupos de Servicio

---

## Preparación de una reunión

Importar Programa Oficial

↓

Crear Programa Local

↓

Revisar contenido

↓

Modificar contenido si es necesario

↓

Asignar Personas

↓

Exportar PDF

---

## Administración de Exhibidores

Crear Lugar

↓

Crear Horarios

↓

Crear Turnos

↓

Asignar Personas

---

# 9. Programa Oficial

El sistema deberá importar el contenido oficial mediante un Motor de Importación.

El contenido oficial siempre permanecerá intacto.

Nunca podrá modificarse.

---

# 10. Programa Local

Todo Programa Local proviene de un Programa Oficial.

El Programa Local podrá modificarse libremente para adaptarse a las necesidades de cada Congregación.

Ejemplos:

- Visita del Superintendente de Circuito.
- Conmemoración.
- Asamblea Regional.
- Asamblea de Circuito.
- Programa Especial.
- Cancelación de reunión.

---

# 11. Motor de Importación

El sistema no dependerá exclusivamente de una fuente.

La arquitectura permitirá incorporar múltiples fuentes de importación.

Ejemplos:

- JW.ORG
- JSON
- CSV
- Manual

---

# 12. Exportación

Todo Programa Local deberá poder exportarse a PDF.

La exportación deberá estar diseñada para impresión y distribución digital.

El PDF será considerado parte del producto y no una funcionalidad secundaria.

---

# 13. Restricciones

El sistema:

- Nunca compartirá información entre Congregaciones.
- Nunca modificará el Programa Oficial.
- Nunca dependerá obligatoriamente de servicios de pago.
- Nunca almacenará lógica de negocio fuera del dominio definido.

---

# 14. Integraciones Futuras

- WhatsApp.
- Copias de seguridad automáticas.
- Calendarios.
- Correo electrónico.
- Notificaciones.
- APIs externas.

---

# 15. Alcance del MVP

Incluye:

## Plataforma

- Login.
- Roles.
- Permisos.
- Usuarios.
- Multitenancy.

---

## Congregación

- Congregaciones.
- Personas.
- Perfiles Locales.
- Privilegios.
- Grupos de Servicio.
- Eventos Especiales.
- Oradores Visitantes.

---

## Reuniones

- Importación.
- Programa Oficial.
- Programa Local.
- Eventos.
- Partes.
- Asignaciones.
- Exportación PDF.

---

## Ministerio

- Exhibidores.
- Lugares.
- Horarios.
- Turnos.
- Asignados.

---

# 16. Fuera del MVP

No harán parte de la primera versión:

- WhatsApp.
- Notificaciones.
- Estadísticas avanzadas.
- Reportes avanzados.
- Aplicación móvil.
- Multiidioma.
- Integraciones con calendarios.
- Automatizaciones complejas.
- Inteligencia Artificial.

---

# 17. Evolución del Producto

La arquitectura deberá permitir incorporar nuevos módulos sin modificar el núcleo del sistema.

Ejemplos futuros:

- Predicación.
- Territorios.
- Informes de Servicio.
- Inventario.
- Biblioteca.
- Asistencia.
- Hospitalidad.
- Eventos adicionales.
- Integraciones externas.

---

# 18. Definición de Éxito del MVP

El MVP será considerado exitoso cuando una Congregación pueda:

- Registrar todas sus Personas.
- Configurar la Congregación.
- Importar un Programa Oficial.
- Adaptarlo localmente.
- Asignar todas las Partes del Programa.
- Administrar Exhibidores.
- Exportar el Programa a PDF.
- Utilizar el sistema durante una semana completa sin depender de herramientas externas.

---

# 19. Visión a Largo Plazo

Reuniones JW será una plataforma modular para la administración integral de Congregaciones, construida sobre un dominio sólido, escalable y desacoplado de cualquier tecnología específica.

Cada nueva funcionalidad deberá integrarse respetando el Modelo de Dominio, las Reglas del Negocio y las Decisiones Arquitectónicas previamente definidas.

El crecimiento del producto nunca deberá comprometer la simplicidad, la mantenibilidad ni la experiencia de los usuarios.
