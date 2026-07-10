# AGENT_RULES.md

# Reuniones JW
## Reglas para Agentes de IA

**Versión:** 1.0  
**Estado:** Aprobado

---

# Objetivo

Este documento define las reglas obligatorias que deberán seguir todos los agentes de inteligencia artificial que participen en el desarrollo de Reuniones JW.

Estas reglas tienen prioridad sobre cualquier decisión automática tomada por una IA.

El objetivo es garantizar consistencia, calidad y estabilidad durante todo el ciclo de vida del proyecto.

---

# PRINCIPIO FUNDAMENTAL

La IA no diseña el producto.

La IA implementa el producto.

Las decisiones de negocio y arquitectura pertenecen a la documentación oficial del proyecto.

---

# Documentación obligatoria

Antes de escribir una sola línea de código, toda IA deberá leer completamente los siguientes documentos, en este orden:

1. README.md
2. AI_CONTEXT.md
3. VISION.md
4. DOMAIN_MAP.md
5. BUSINESS_RULES.md
6. DOMAIN_MODEL.md
7. PRODUCT.md
8. BACKLOG.md
9. IMPLEMENTATION_PLAN.md
10. ARCHITECTURE.md
11. DATABASE.md
12. SECURITY.md
13. CONVENTIONS.md
14. DECISIONS.md

Si existe contradicción entre documentos, deberá reportarse antes de implementar cualquier cambio.

---

# Arquitectura

La IA nunca deberá:

- modificar la arquitectura sin aprobación;
- crear nuevas entidades sin actualizar DOMAIN_MODEL.md;
- modificar reglas del negocio;
- romper el aislamiento entre Congregaciones;
- introducir dependencias innecesarias;
- introducir patrones de diseño injustificados.

Toda propuesta arquitectónica deberá justificarse técnicamente.

---

# Dominio

La IA deberá respetar siempre el lenguaje del negocio.

Ejemplos:

Correcto:

- Persona
- Congregación
- Programa
- Asignación
- Perfil Local
- Privilegio
- Exhibidores

Evitar utilizar terminología distinta en la documentación funcional.

---

# Laravel

El proyecto utilizará Laravel como framework principal.

La IA deberá seguir las convenciones oficiales del framework.

No deberá reinventar soluciones ya proporcionadas por Laravel.

Siempre priorizar:

- Policies
- Form Requests
- Eloquent
- Migrations
- Seeders
- Factories
- Jobs
- Events
- Notifications

cuando sean apropiados.

---

# Bootstrap

La interfaz utilizará Bootstrap 5.

No introducir:

- Tailwind
- Bulma
- Foundation
- Material UI
- DaisyUI

No mezclar frameworks CSS.

---

# JavaScript

Preferir JavaScript nativo.

Solo introducir librerías cuando exista una justificación clara.

---

# Base de Datos

Toda modificación deberá respetar:

DATABASE.md

No crear tablas fuera del dominio aprobado.

No duplicar información.

No introducir relaciones innecesarias.

---

# Seguridad

Toda funcionalidad deberá respetar SECURITY.md.

Nunca asumir permisos.

Toda acción deberá validarse mediante Roles y Permisos.

---

# Código

El código deberá ser:

- limpio;
- legible;
- desacoplado;
- reutilizable;
- documentado cuando sea necesario.

Evitar complejidad innecesaria.

---

# Git

Toda modificación deberá realizarse mediante ramas.

Nunca trabajar directamente sobre main.

Toda funcionalidad deberá finalizar mediante Pull Request.

---

# Pull Requests

Toda IA deberá:

explicar:

- qué hizo;
- por qué lo hizo;
- qué archivos modificó;
- riesgos conocidos;
- posibles mejoras futuras.

---

# Tests

Toda funcionalidad deberá incluir pruebas cuando sea posible.

No entregar funcionalidades críticas sin validación.

---

# Changelog

Toda modificación funcional deberá actualizar:

CHANGELOG_AI.md

Indicando:

- fecha;
- funcionalidad;
- motivo;
- impacto.

---

# Dependencias

No instalar paquetes sin justificación.

Antes de instalar una dependencia nueva deberá responder:

- ¿Laravel ya resuelve este problema?
- ¿PHP ya resuelve este problema?
- ¿Bootstrap ya resuelve este problema?

Si la respuesta es sí, no instalar una dependencia.

---

# Rendimiento

Evitar:

- consultas N+1;
- código duplicado;
- consultas innecesarias;
- carga excesiva de JavaScript.

Siempre priorizar rendimiento.

---

# Experiencia de Usuario

El sistema deberá ser intuitivo.

Las interfaces deberán hablar el lenguaje de la congregación.

No utilizar lenguaje técnico para el usuario final.

---

# Accesibilidad

Toda interfaz deberá considerar:

- navegación por teclado;
- contraste adecuado;
- etiquetas accesibles;
- mensajes claros.

---

# PDF

Toda exportación PDF deberá respetar el formato definido por el proyecto.

La exportación forma parte del producto.

No es una funcionalidad secundaria.

---

# Motor de Importación

Nunca acoplar el sistema exclusivamente a JW.ORG.

Toda implementación deberá permitir incorporar nuevas fuentes de importación.

---

# Congregaciones

Toda información del negocio pertenece a una Congregación.

Nunca compartir información automáticamente entre Congregaciones.

---

# Personas

No todas las Personas tienen Usuario.

Nunca asumir que una Persona puede iniciar sesión.

---

# Programas

El Programa Oficial nunca podrá modificarse.

Toda modificación deberá realizarse sobre el Programa Local.

---

# Exhibidores

Los Exhibidores pertenecen al dominio Ministerio.

No mezclarlos con la programación de reuniones.

---

# Filosofía

La tecnología debe adaptarse al funcionamiento de la congregación.

Nunca la congregación deberá adaptarse al software.

---

# Si existe una duda

La IA deberá:

1. detener la implementación;
2. consultar la documentación;
3. proponer alternativas;
4. esperar aprobación antes de continuar.

Nunca deberá inventar reglas del negocio.

---

# Regla Final

El funcionamiento correcto del código no es suficiente.

Todo desarrollo deberá respetar:

- la Visión;
- el Modelo de Dominio;
- las Reglas del Negocio;
- la Arquitectura;
- la Filosofía del Proyecto.

El incumplimiento de cualquiera de estos principios se considera un defecto del desarrollo, incluso si el código funciona correctamente.
