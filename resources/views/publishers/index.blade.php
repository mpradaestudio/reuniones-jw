@extends('layouts.app')

@section('title', 'Publicadores')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 mb-0 text-dark">Publicadores</h2>
        @can('create', \App\Models\Publisher::class)
            <a href="{{ route('publishers.create') }}" class="btn btn-dark">Añadir publicador</a>
        @endcan
    </div>

    {{-- Filtros y búsqueda --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('publishers.index') }}" class="row g-2 align-items-end">

                {{-- Búsqueda libre: nombre / apellidos --}}
                <div class="col-12 col-md-4">
                    <label for="q" class="form-label small text-secondary mb-1">Buscar</label>
                    <input type="text" id="q" name="q" value="{{ $filters['q'] }}"
                           class="form-control" placeholder="Nombre o apellidos">
                </div>

                {{-- Estado --}}
                <div class="col-6 col-md-2">
                    <label for="estado" class="form-label small text-secondary mb-1">Estado</label>
                    <select id="estado" name="estado" class="form-select">
                        <option value="">Todos</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" @selected($filters['estado'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Privilegio --}}
                <div class="col-6 col-md-2">
                    <label for="privilegio" class="form-label small text-secondary mb-1">Privilegio</label>
                    <select id="privilegio" name="privilegio" class="form-select">
                        <option value="">Todos</option>
                        @foreach($privileges as $value => $label)
                            <option value="{{ $value }}" @selected($filters['privilegio'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Género --}}
                <div class="col-6 col-md-2">
                    <label for="genero" class="form-label small text-secondary mb-1">Género</label>
                    <select id="genero" name="genero" class="form-select">
                        <option value="">Todos</option>
                        <option value="masculino" @selected($filters['genero'] === 'masculino')>Masculino</option>
                        <option value="femenino"  @selected($filters['genero'] === 'femenino')>Femenino</option>
                    </select>
                </div>

                {{-- Congregación (solo SuperAdministrador) --}}
                @if($isSuperAdmin)
                    <div class="col-6 col-md-2">
                        <label for="congregation" class="form-label small text-secondary mb-1">Congregación</label>
                        <select id="congregation" name="congregation" class="form-select">
                            <option value="">Todas</option>
                            @foreach($congregations as $congregation)
                                <option value="{{ $congregation->id }}"
                                    @selected($filters['congregation'] !== '' && (int) $filters['congregation'] === $congregation->id)>
                                    {{ $congregation->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Acciones del formulario --}}
                <div class="col-12 col-md-{{ $isSuperAdmin ? 2 : 2 }} d-flex gap-2">
                    <button type="submit" class="btn btn-dark flex-grow-1">Filtrar</button>
                    <a href="{{ route('publishers.index') }}" class="btn btn-outline-secondary">Limpiar</a>
                </div>

            </form>
        </div>
    </div>

    {{-- Tabla de resultados --}}
    <div class="card shadow-sm border-0">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h6 mb-0 text-dark">Resultados</h2>
                <span class="text-secondary small">{{ $publishers->total() }} publicador(es)</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Nombre</th>
                            <th scope="col">Privilegio</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Género</th>
                            <th scope="col">Bautizado</th>
                            @if($isSuperAdmin)
                                <th scope="col">Congregación</th>
                            @endif
                            <th scope="col" class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($publishers as $publisher)
                            <tr>
                                <td>
                                    <span class="fw-medium text-dark">{{ $publisher->nombre_completo }}</span>
                                    @if($publisher->user)
                                        <div class="text-secondary" style="font-size: .75rem;">{{ $publisher->user->email }}</div>
                                    @endif
                                </td>

                                <td>
                                    <span class="badge {{ $publisher->privilegio->badgeClass() }}">
                                        {{ $publisher->privilegio->label() }}
                                    </span>
                                </td>

                                <td>
                                    <span class="badge {{ $publisher->estado->badgeClass() }}">
                                        {{ $publisher->estado->label() }}
                                    </span>
                                </td>

                                <td class="text-secondary text-capitalize">{{ $publisher->genero }}</td>

                                <td class="text-secondary">
                                    {{ $publisher->fecha_bautismo?->format('d/m/Y') ?? '—' }}
                                </td>

                                @if($isSuperAdmin)
                                    <td class="text-secondary">{{ $publisher->congregation?->nombre ?? '—' }}</td>
                                @endif

                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        @can('toggleStatus', $publisher)
                                            <form method="POST"
                                                  action="{{ route('publishers.toggle-status', $publisher) }}"
                                                  class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                @if($publisher->isActive())
                                                    <input type="hidden" name="estado"
                                                           value="{{ \App\Enums\PublisherStatus::Irregular->value }}">
                                                    <button type="submit"
                                                            class="btn btn-sm btn-outline-warning">Marcar irregular</button>
                                                @else
                                                    <input type="hidden" name="estado"
                                                           value="{{ \App\Enums\PublisherStatus::Active->value }}">
                                                    <button type="submit"
                                                            class="btn btn-sm btn-outline-success">Activar</button>
                                                @endif
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isSuperAdmin ? 7 : 6 }}"
                                    class="text-center text-secondary py-4">
                                    No se encontraron publicadores con los criterios indicados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($publishers->hasPages())
                <div class="mt-3">
                    {{ $publishers->links() }}
                </div>
            @endif

        </div>
    </div>
@endsection
