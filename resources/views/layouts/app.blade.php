<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel') · {{ config('app.name', 'Reuniones JW') }}</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    {{-- Tipografía: Google Sans Flex (stack de UI aprobado) con alternativas seguras --}}
    <style>
        :root {
            --jw-sidebar-bg: #1f2937;
            --jw-sidebar-bg-active: #111827;
            --bs-body-font-family: "Google Sans Flex", "Google Sans", "Product Sans", system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        }
        body { font-family: var(--bs-body-font-family); }
        .jw-sidebar {
            background-color: var(--jw-sidebar-bg);
            min-height: 100vh;
            width: 260px;
        }
        .jw-sidebar .nav-link { color: #cbd5e1; border-radius: .375rem; }
        .jw-sidebar .nav-link:hover { background-color: #374151; color: #fff; }
        .jw-sidebar .nav-link.active { background-color: var(--jw-sidebar-bg-active); color: #fff; }
        @media (max-width: 991.98px) {
            .jw-sidebar { position: fixed; z-index: 1045; transform: translateX(-100%); transition: transform .2s ease-in-out; }
            .jw-sidebar.show { transform: translateX(0); }
        }
    </style>
</head>
<body class="bg-light">
<div class="d-flex">
    {{-- Barra lateral --}}
    <aside id="sidebar" class="jw-sidebar text-light flex-shrink-0 d-flex flex-column">
        <div class="d-flex align-items-center px-4 border-bottom border-secondary" style="height: 64px;">
            <span class="fs-5 fw-semibold text-white">Reuniones JW</span>
        </div>

        @php($currentCongregation = $currentCongregation ?? null)
        @if($currentCongregation)
            <div class="px-4 py-2 small text-uppercase text-secondary">
                {{ $currentCongregation->nombre }}
            </div>
        @endif

        <nav class="nav flex-column gap-1 p-3">
            @php($nav = [
                ['route' => 'dashboard', 'label' => 'Dashboard', 'can' => 'dashboard.view'],
                ['route' => 'congregations.index', 'label' => 'Congregaciones', 'can' => 'congregations.view'],
                ['route' => 'users.index', 'label' => 'Usuarios', 'can' => 'users.view'],
                ['route' => 'roles.index', 'label' => 'Roles', 'can' => 'roles.view'],
                ['route' => 'settings.index', 'label' => 'Configuración', 'can' => null],
            ])

            @foreach($nav as $item)
                @if($item['can'] === null || auth()->user()->can($item['can']))
                    <a href="{{ route($item['route']) }}"
                       class="nav-link px-3 py-2 small fw-medium {{ request()->routeIs($item['route']) ? 'active' : '' }}">
                        {{ $item['label'] }}
                    </a>
                @endif
            @endforeach
        </nav>
    </aside>

    {{-- Contenido --}}
    <div class="flex-grow-1 min-vw-0 d-flex flex-column">
        {{-- Barra superior --}}
        <header class="d-flex align-items-center justify-content-between bg-white border-bottom px-3 px-lg-4" style="height: 64px;">
            <button class="btn btn-outline-secondary d-lg-none" type="button" onclick="toggleSidebar()" aria-label="Menú">
                <span class="navbar-toggler-icon"></span>
            </button>

            <h1 class="h5 mb-0 text-dark">@yield('title', 'Panel')</h1>

            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-sm-block">
                    <p class="mb-0 small fw-medium text-dark">{{ auth()->user()->nombre_completo }}</p>
                    <p class="mb-0 text-secondary" style="font-size: .75rem;">{{ auth()->user()->getRoleNames()->first() }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mb-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-light border">Cerrar sesión</button>
                </form>
            </div>
        </header>

        <main class="flex-grow-1 p-3 p-lg-4">
            @yield('content')
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('show');
    }
</script>
</body>
</html>
