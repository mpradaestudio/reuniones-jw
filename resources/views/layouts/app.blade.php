<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel') · {{ config('app.name', 'Reuniones JW') }}</title>

    {{-- Tipografía: Google Sans Flex (con fallback al sistema vía app.css) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans+Flex&display=swap" rel="stylesheet">

    {{-- Bootstrap 5 (framework oficial de UI) + iconos --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    {{-- Estilos globales del proyecto (variables CSS, navbar, sidebar, footer) --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    @stack('styles')
</head>
<body>
    @include('layouts.partials.navbar')

    <div class="container-fluid">
        <div class="row">
            {{-- Sidebar (offcanvas en móvil, fijo en escritorio) --}}
            <div class="col-lg-auto px-0">
                @include('layouts.partials.sidebar')
            </div>

            {{-- Contenido principal + footer --}}
            <div class="col rjw-content px-0">
                <main class="rjw-main">
                    @hasSection('page-heading')
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <h1 class="h4 mb-0 text-dark-emphasis">@yield('page-heading')</h1>
                            @yield('page-actions')
                        </div>
                    @endif

                    @yield('content')
                </main>

                @include('layouts.partials.footer')
            </div>
        </div>
    </div>

    {{-- Bootstrap 5 JS bundle --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>
</html>
