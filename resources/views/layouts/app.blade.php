<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel') · {{ config('app.name', 'Reuniones JW') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full">
<div x-data class="min-h-full">
    <div id="app" class="flex min-h-screen">
        {{-- Overlay para móvil --}}
        <div id="sidebar-overlay"
             class="fixed inset-0 z-30 hidden bg-gray-900/50 lg:hidden"
             onclick="toggleSidebar()"></div>

        {{-- Barra lateral --}}
        <aside id="sidebar"
               class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full transform bg-slate-800 text-slate-200 transition-transform duration-200 ease-in-out lg:static lg:translate-x-0">
            <div class="flex h-16 items-center gap-2 border-b border-slate-700 px-6">
                <span class="text-lg font-semibold text-white">Reuniones JW</span>
            </div>

            @php($currentCongregation = $currentCongregation ?? null)
            @if($currentCongregation)
                <div class="px-6 py-3 text-xs uppercase tracking-wide text-slate-400">
                    {{ $currentCongregation->nombre }}
                </div>
            @endif

            <nav class="space-y-1 px-3 py-4">
                @php($nav = [
                    ['route' => 'dashboard', 'label' => 'Dashboard', 'can' => 'dashboard.view'],
                    ['route' => 'congregations.index', 'label' => 'Congregaciones', 'can' => 'congregations.view'],
                    ['route' => 'users.index', 'label' => 'Usuarios', 'can' => 'users.view'],
                    ['route' => 'roles.index', 'label' => 'Roles', 'can' => 'roles.view'],
                    ['route' => 'settings.index', 'label' => 'Configuración', 'can' => null],
                ])

                @foreach($nav as $item)
                    @if($item['can'] === null || auth()->user()->can($item['can']))
                        @php($active = request()->routeIs($item['route']))
                        <a href="{{ route($item['route']) }}"
                           class="block rounded-md px-3 py-2 text-sm font-medium transition
                                  {{ $active ? 'bg-slate-900 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                            {{ $item['label'] }}
                        </a>
                    @endif
                @endforeach
            </nav>
        </aside>

        {{-- Contenido --}}
        <div class="flex min-w-0 flex-1 flex-col">
            {{-- Barra superior --}}
            <header class="flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 lg:px-8">
                <button class="text-gray-500 lg:hidden" onclick="toggleSidebar()" aria-label="Menú">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <h1 class="text-lg font-semibold text-gray-800">@yield('title', 'Panel')</h1>

                <div class="flex items-center gap-4">
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-medium text-gray-700">{{ auth()->user()->nombre_completo }}</p>
                        <p class="text-xs text-gray-500">{{ auth()->user()->getRoleNames()->first() }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="rounded-md bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            </header>

            <main class="flex-1 p-4 lg:p-8">
                @yield('content')
            </main>
        </div>
    </div>
</div>

<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('-translate-x-full');
        document.getElementById('sidebar-overlay').classList.toggle('hidden');
    }
</script>
</body>
</html>
