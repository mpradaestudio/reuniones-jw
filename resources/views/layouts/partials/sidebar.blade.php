{{-- Menú lateral. Offcanvas en móvil, fijo (offcanvas-lg) en escritorio. --}}
@php($navItems = [
    ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'can' => 'dashboard.view'],
    ['route' => 'congregations.index', 'label' => 'Congregaciones', 'icon' => 'bi-building', 'can' => 'congregations.view'],
    ['route' => 'users.index', 'label' => 'Usuarios', 'icon' => 'bi-people', 'can' => 'users.view'],
    ['route' => 'roles.index', 'label' => 'Roles', 'icon' => 'bi-shield-lock', 'can' => 'roles.view'],
    ['route' => 'settings.index', 'label' => 'Configuración', 'icon' => 'bi-gear', 'can' => null],
])

<div class="offcanvas-lg offcanvas-start rjw-sidebar text-white" tabindex="-1" id="sidebarMenu"
     aria-labelledby="sidebarMenuLabel">
    <div class="offcanvas-header border-bottom border-secondary-subtle">
        <h5 class="offcanvas-title" id="sidebarMenuLabel">Menú</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
                data-bs-target="#sidebarMenu" aria-label="Cerrar"></button>
    </div>

    <div class="offcanvas-body d-flex flex-column p-3">
        <p class="sidebar-heading mb-2 d-none d-lg-block">Navegación</p>
        <ul class="nav nav-pills flex-column mb-auto">
            @foreach($navItems as $item)
                @if($item['can'] === null || auth()->user()->can($item['can']))
                    <li class="nav-item">
                        <a href="{{ route($item['route']) }}"
                           class="nav-link {{ request()->routeIs($item['route']) ? 'active' : '' }}">
                            <i class="bi {{ $item['icon'] }}"></i>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    </li>
                @endif
            @endforeach
        </ul>
    </div>
</div>
