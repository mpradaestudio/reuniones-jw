@extends('layouts.app')

@section('title', 'Detalle de rol')

@section('content')
    <div class="mb-3">
        <a href="{{ route('roles.index') }}" class="text-decoration-none small text-secondary">&larr; Volver al listado</a>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h2 class="h5 text-dark mb-1">
                        {{ $role->name }}
                        @if($role->isSystem())
                            <span class="badge text-bg-secondary align-middle">Sistema</span>
                        @else
                            <span class="badge text-bg-info align-middle">Personalizado</span>
                        @endif
                    </h2>
                    <p class="text-secondary small mb-0">{{ $role->description ?: 'Sin descripción.' }}</p>
                </div>
                <div class="d-inline-flex gap-2">
                    @can('update', $role)
                        <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                    @endcan
                    @can('duplicate', $role)
                        <a href="{{ route('roles.duplicate-form', $role) }}" class="btn btn-sm btn-outline-secondary">Duplicar</a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h3 class="h6 text-dark mb-3">Permisos ({{ $role->permissions->count() }})</h3>
                    @forelse($groupedPermissions as $module => $permissions)
                        <div class="mb-3">
                            <p class="text-uppercase text-secondary small fw-semibold mb-1">{{ $module }}</p>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($permissions as $permission)
                                    <span class="badge text-bg-light border">{{ $permission }}</span>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <p class="text-secondary small mb-0">Este rol no tiene permisos asignados.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h3 class="h6 text-dark mb-3">Usuarios con este rol ({{ $role->users->count() }})</h3>
                    @forelse($role->users as $user)
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span class="text-dark small">{{ $user->nombre_completo }}</span>
                            <span class="text-secondary small">{{ $user->email }}</span>
                        </div>
                    @empty
                        <p class="text-secondary small mb-0">Ningún usuario tiene asignado este rol.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
