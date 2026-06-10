@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
    @php($isSuperAdmin = auth()->user()->isSuperAdmin())

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 mb-0 text-dark">Usuarios</h2>
        @can('create', \App\Models\User::class)
            <a href="{{ route('users.create') }}" class="btn btn-dark">Crear usuario</a>
        @endcan
    </div>

    {{-- Búsqueda y filtros --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('users.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-5">
                    <label for="q" class="form-label small text-secondary mb-1">Buscar</label>
                    <input type="text" id="q" name="q" value="{{ $filters['q'] }}"
                           class="form-control" placeholder="Nombre, apellidos o correo">
                </div>

                <div class="col-6 col-md-3">
                    <label for="estado" class="form-label small text-secondary mb-1">Estado</label>
                    <select id="estado" name="estado" class="form-select">
                        <option value="">Todos</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" @selected($filters['estado'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label for="role" class="form-label small text-secondary mb-1">Rol</label>
                    <select id="role" name="role" class="form-select">
                        <option value="">Todos</option>
                        @foreach($roles as $roleName)
                            <option value="{{ $roleName }}" @selected($filters['role'] === $roleName)>{{ $roleName }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-dark flex-grow-1">Filtrar</button>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Resultados --}}
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h6 mb-0 text-dark">Resultados</h2>
                <span class="text-secondary small">{{ $users->total() }} resultado(s)</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Nombre</th>
                            <th scope="col">Correo</th>
                            <th scope="col">Rol</th>
                            @if($isSuperAdmin)
                                <th scope="col">Congregación</th>
                            @endif
                            <th scope="col">Estado</th>
                            <th scope="col" class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td class="fw-medium text-dark">{{ $user->nombre_completo }}</td>
                                <td class="text-secondary">{{ $user->email }}</td>
                                <td>
                                    @php($roleName = $user->getRoleNames()->first())
                                    @if($roleName)
                                        <span class="badge text-bg-light border">{{ $roleName }}</span>
                                    @else
                                        <span class="text-secondary small">—</span>
                                    @endif
                                </td>
                                @if($isSuperAdmin)
                                    <td class="text-secondary">{{ $user->congregation?->nombre ?? '—' }}</td>
                                @endif
                                <td>
                                    @php($estadoClass = $user->estado === \App\Enums\UserStatus::Active ? 'text-bg-success' : 'text-bg-secondary')
                                    <span class="badge {{ $estadoClass }}">{{ $user->estado->label() }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        @can('update', $user)
                                            <a href="{{ route('users.edit', $user) }}"
                                               class="btn btn-sm btn-outline-secondary">Editar</a>
                                        @endcan

                                        @can('toggleStatus', $user)
                                            <form method="POST" action="{{ route('users.toggle-status', $user) }}"
                                                  class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                @if($user->estado === \App\Enums\UserStatus::Active)
                                                    <button type="submit" class="btn btn-sm btn-outline-warning">Desactivar</button>
                                                @else
                                                    <button type="submit" class="btn btn-sm btn-outline-success">Activar</button>
                                                @endif
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isSuperAdmin ? 6 : 5 }}" class="text-center text-secondary py-4">
                                    No se encontraron usuarios con los criterios indicados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="mt-3">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
