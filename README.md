# Reuniones JW

Aplicación web **multi-congregación** para la gestión de congregaciones (usuarios,
roles, permisos y, en fases futuras, horarios, programación semanal, asignaciones
y discursos).

> Estado actual: **estructura base** (Laravel 12, MySQL, Spatie Permission,
> DomPDF, autenticación con validación estricta de tenant y dashboard básico).
> Los CRUD de negocio aún **no** están implementados.

---

## Stack

- PHP 8.2+
- Laravel 12
- MySQL
- Apache (XAMPP) en local
- [spatie/laravel-permission](https://github.com/spatie/laravel-permission) (RBAC)
- [barryvdh/laravel-dompdf](https://github.com/barryvdh/laravel-dompdf) (PDF)

La documentación de arquitectura está en [`docs/ANALISIS.md`](docs/ANALISIS.md),
las decisiones en [`docs/DECISIONES.md`](docs/DECISIONES.md) y las convenciones del
proyecto en [`CLAUDE.md`](CLAUDE.md).

---

## Requisitos previos (XAMPP)

1. Instalar [XAMPP](https://www.apachefriends.org/) (Apache + MySQL + PHP 8.2+).
2. Instalar [Composer](https://getcomposer.org/).
3. Asegurar que las extensiones PHP estén activas en `php.ini`:
   `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`,
   `fileinfo`, `curl`, `gd`, `zip`.

---

## Instalación

```bash
# 1. Clonar el repositorio
git clone https://github.com/mpradaestudio/reuniones-jw.git
cd reuniones-jw

# 2. Instalar dependencias
composer install

# 3. Variables de entorno
copy .env.example .env      # Windows
# cp .env.example .env      # Linux/macOS

# 4. Generar la clave de la aplicación
php artisan key:generate
```

### Base de datos (MySQL / phpMyAdmin)

1. Inicia **Apache** y **MySQL** desde el panel de XAMPP.
2. En phpMyAdmin (`http://localhost/phpmyadmin`) crea una base de datos llamada
   `reuniones_jw` (cotejamiento `utf8mb4_unicode_ci`).
3. Configura el `.env`:

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=reuniones_jw
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. Ejecuta migraciones y seeders:

   ```bash
   php artisan migrate --seed
   ```

---

## Acceso por subdominio (multi-congregación)

El tenant se resuelve por **subdominio**. En local, configura los subdominios.

### 1. Archivo `hosts`

- Windows: `C:\Windows\System32\drivers\etc\hosts`
- Linux/macOS: `/etc/hosts`

```
127.0.0.1   reuniones-jw.local
127.0.0.1   central.reuniones-jw.local
127.0.0.1   norte.reuniones-jw.local
```

### 2. VirtualHost de Apache (XAMPP)

En `xampp/apache/conf/extra/httpd-vhosts.conf`:

```apache
<VirtualHost *:80>
    ServerName reuniones-jw.local
    ServerAlias *.reuniones-jw.local
    DocumentRoot "C:/xampp/htdocs/reuniones-jw/public"
    <Directory "C:/xampp/htdocs/reuniones-jw/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Reinicia Apache. Ajusta `APP_URL` y `APP_DOMAIN` en `.env`:

```env
APP_URL=http://reuniones-jw.local
APP_DOMAIN=reuniones-jw.local
SESSION_DOMAIN=.reuniones-jw.local
```

> Alternativa rápida (sin Apache): `php artisan serve`. En ese caso accede por
> `http://127.0.0.1:8000`. La resolución por subdominio requiere los `hosts` y un
> proxy/host con subdominio; para desarrollo simple, el SuperAdministrador puede
> entrar por el dominio base.

---

## Usuarios de demostración

Los seeders crean estos usuarios (contraseña: `password`). **Cámbialos en
producción.**

| Rol                        | Email                                  | Acceso                         |
|----------------------------|----------------------------------------|--------------------------------|
| SuperAdministrador         | `superadmin@reuniones-jw.local`        | Dominio base (global)          |
| AdministradorCongregacion  | `admin.central@reuniones-jw.local`     | `central.reuniones-jw.local`   |
| Usuario                    | `usuario.central@reuniones-jw.local`   | `central.reuniones-jw.local`   |
| AdministradorCongregacion  | `admin.norte@reuniones-jw.local`       | `norte.reuniones-jw.local`     |
| Usuario                    | `usuario.norte@reuniones-jw.local`     | `norte.reuniones-jw.local`     |

> Validación estricta de tenant: cada usuario **solo** puede iniciar sesión en el
> subdominio de su congregación. El SuperAdministrador es la única excepción.

---

## Estructura relevante

```
app/
├── Enums/                      CongregationStatus, UserStatus (estados como enum)
├── Http/
│   ├── Controllers/            Auth, Dashboard, Placeholder (módulos pendientes)
│   ├── Middleware/             IdentifyCongregation (resuelve tenant por subdominio)
│   └── Requests/Auth/          LoginRequest (validación estricta de tenant)
├── Models/
│   ├── Concerns/               BelongsToCongregation (trait para modelos de negocio)
│   ├── Scopes/                 CongregationScope (Global Scope multi-tenant)
│   ├── Congregation.php        (SoftDeletes)
│   ├── User.php                (HasRoles de Spatie)
│   └── AuditLog.php
├── Support/                    Tenant (congregación activa de la petición)
database/
├── migrations/                 users, congregations, audit_logs, permisos (Spatie)
└── seeders/                    RolePermissionSeeder, CongregationSeeder, UserSeeder
resources/views/
├── auth/login.blade.php
├── dashboard.blade.php
├── layouts/app.blade.php       (menú lateral responsive en español)
├── placeholder.blade.php
└── pdf/                        (plantillas Blade para reportes DomPDF)
```

---

## Comandos útiles

```bash
php artisan migrate:fresh --seed   # Reconstruir BD + datos demo
php artisan optimize:clear         # Limpiar cachés
php artisan permission:cache-reset # Limpiar caché de permisos (Spatie)
```

---

## Roadmap

Ver el detalle en [`docs/ANALISIS.md`](docs/ANALISIS.md) (sección Roadmap):
módulos de Congregaciones, Usuarios, Roles, horarios, programación semanal,
asignaciones, discursos y reportes PDF.
