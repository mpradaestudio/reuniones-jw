@extends('layouts.app')

@section('title', 'Congregaciones')
@section('page-heading', 'Congregaciones')

@section('page-actions')
    @can('congregations.create')
        <a href="{{ route('congregations.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Nueva congregación
        </a>
    @endcan
@endsection

@section('content')
    @php($badge = ['active' => 'success', 'inactive' => 'secondary', 'suspended' => 'warning'])

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    {{-- Pestañas activas / archivadas --}}
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ ! $trashed ? 'active' : '' }}" href="{{ route('congregations.index') }}">
                Activas
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $trashed ? 'active' : '' }}" href="{{ route('congregations.index', ['archivadas' => 1]) }}">
                <i class="bi bi-archive me-1"></i> Archivadas
            </a>
        </li>
    </ul>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('congregations.index') }}" class="row g-2 mb-3">
        @if($trashed)<input type="hidden" name="archivadas" value="1">@endif
        <div class="col-12 col-sm-6 col-md-5">
            <input type="text" name="q" value="{{ $search }}" class="form-control"
                   placeholder="Buscar por nombre o subdominio">
        </div>
        <div class="col-8 col-sm-4 col-md-3">
            <select name="estado" class="form-select">
                <option value="">Todos los estados</option>
                @foreach($statuses as $value => $label)
                    <option value="{{ $value }}" @selected($estado === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-4 col-sm-2">
            <button type="submit" class="btn btn-outline-secondary w-100">Filtrar</button>
        </div>
    </form>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Nombre</th>
                        <th scope="col">Subdominio</th>
                        <th scope="col">Estado</th>
                        <th scope="col">{{ $trashed ? 'Archivada' : 'Creada' }}</th>
                        <th scope="col" class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($congregations as $congregation)
                        <tr>
                            <td class="fw-medium">{{ $congregation->nombre }}</td>
                            <td><code>{{ $congregation->subdominio }}</code></td>
                            <td>
                                <span class="badge text-bg-{{ $badge[$congregation->estado->value] ?? 'secondary' }}">
                                    {{ $congregation->estado->label() }}
                                </span>
                            </td>
                            <td class="text-muted small">
                                {{ ($trashed ? $congregation->deleted_at : $congregation->created_at)?->format('d/m/Y H:i') }}
                            </td>
                            <td class="text-end">
                                @if($trashed)
                                    @can('congregations.delete')
                                        <form method="POST" action="{{ route('congregations.restore', $congregation) }}" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-success">
                                                <i class="bi bi-arrow-counterclockwise me-1"></i> Restaurar
                                            </button>
                                        </form>
                                    @endcan
                                @else
                                    <div class="btn-group">
                                        @can('congregations.update')
                                            <a href="{{ route('congregations.edit', $congregation) }}"
                                               class="btn btn-sm btn-outline-secondary" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan

                                        @can('congregations.toggle-status')
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                Estado
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                @foreach($statuses as $value => $label)
                                                    <li>
                                                        <form method="POST" action="{{ route('congregations.status', $congregation) }}">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="estado" value="{{ $value }}">
                                                            <button type="submit" class="dropdown-item @disabled($congregation->estado->value === $value)"
                                                                    @disabled($congregation->estado->value === $value)>
                                                                {{ $label }}
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endcan

                                        @can('congregations.delete')
                                            <form method="POST" action="{{ route('congregations.destroy', $congregation) }}" class="d-inline"
                                                  onsubmit="return confirm('¿Archivar la congregación «{{ $congregation->nombre }}»? Podrá restaurarla después.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Archivar">
                                                    <i class="bi bi-archive"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                No hay congregaciones que mostrar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $congregations->links() }}
    </div>
@endsection
