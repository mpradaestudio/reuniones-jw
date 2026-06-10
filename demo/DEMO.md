# Demostración visual — Módulo Congregaciones

Capturas **HTML autocontenidas** de las pantallas reales del módulo (markup y datos
reales generados desde la aplicación). Cada archivo incluye Bootstrap 5 y
Google Sans Flex por CDN y el `app.css` del proyecto **incrustado**, por lo que se
ven tal cual al abrirlos en un navegador con conexión a internet.

> Nota: en este entorno no hay navegador para generar imágenes PNG; estas capturas
> HTML reproducen fielmente la interfaz. Ábrelas localmente con doble clic o
> arrástralas al navegador. (Los formularios apuntan al host real, así que aquí son
> solo vista previa estática.)

## Cómo verlas

- **Opción A:** descarga la carpeta `demo/` (botón *Download raw* en GitHub o
  `git fetch` + checkout de esta rama) y abre los `.html` en tu navegador.
- **Opción B (recomendada para ver todo en vivo):** ejecuta el proyecto en local
  (`php artisan serve` o XAMPP) e inicia sesión como
  `superadmin@reuniones-jw.local` / `password`.

## Índice de capturas

| Archivo | Pantalla | Qué muestra |
|---------|----------|-------------|
| `01-login.html` | Acceso | Login con Bootstrap + Google Sans Flex (layout `guest`). |
| `02-dashboard.html` | Dashboard | Tarjetas de métricas enlazables + panel "Últimas congregaciones". |
| `03-listado.html` | Listado | Tabla, navbar, sidebar, footer, badges de estado, menú de acciones, pestañas Activas/Archivadas, paginación. |
| `04-busqueda.html` | Búsqueda | Resultado de `?q=norte` (solo "Congregación Norte"). |
| `05-filtro-estado.html` | Filtro | Resultado de `?estado=suspended` (solo "Congregación Este"). |
| `06-crear.html` | Crear | Formulario de creación (nombre, subdominio con sufijo, estado). |
| `07-editar.html` | Editar | Formulario de edición precargado de "Congregación Central". |
| `08-archivadas.html` | Papelera | Congregación archivada (SoftDelete) con acción **Restaurar**. |

Datos de demostración usados: Central, Norte, Sur (activa), Este (suspendida),
Oeste (inactiva) y Antigua (archivada).
