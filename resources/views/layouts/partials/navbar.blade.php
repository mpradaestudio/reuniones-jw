{{-- Barra superior --}}
<nav class="navbar navbar-expand-lg navbar-dark rjw-navbar sticky-top">
    <div class="container-fluid">
        {{-- Toggler del sidebar (solo móvil) --}}
        <button class="navbar-toggler border-0 me-2 d-lg-none" type="button"
                data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu"
                aria-controls="sidebarMenu" aria-label="Abrir menú">
            <span class="navbar-toggler-icon"></span>
        </button>

        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('dashboard') }}">
            <i class="bi bi-people-fill"></i>
            <span>Reuniones JW</span>
        </a>

        @auth
            <div class="ms-auto dropdown">
                <button class="btn btn-link text-white text-decoration-none dropdown-toggle d-flex align-items-center gap-2"
                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle fs-5"></i>
                    <span class="d-none d-sm-inline text-start">
                        <span class="d-block lh-1">{{ auth()->user()->nombre_completo }}</span>
                        <small class="text-white-50">{{ auth()->user()->getRoleNames()->first() }}</small>
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    @isset($currentCongregation)
                        <li><span class="dropdown-item-text small text-muted">{{ $currentCongregation->nombre }}</span></li>
                        <li><hr class="dropdown-divider"></li>
                    @endisset
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        @endauth
    </div>
</nav>
