@extends('layouts.app')

@section('title', 'Roles y permisos')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 mb-0 text-dark">Roles y permisos</h2>
        @can('create', \App\Models\Role::class)
            <a href="{{ route('roles.create') }}" class="btn btn-dark">Crear rol</a>
        @endcan
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Rol</th>
                            <th scope="col" class="text-center">Permisos</th>
                            <th scope="col" class="text-center">Usuarios</th>
                            <th scope="col">Tipo</th>
                            <th scope="col" class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                            <tr>
                                <td>
                                    <a href="{{ route('roles.show', $role) }}" class="fw-medium text-dark text-decoration-none">
                                        {{ $role->name }}
                                    </a>
                                    @if($role->description)
                                        <div class="text-secondary small">{{ $role->description }}</div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge text-bg-light border">{{ $role->permissions_count }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge text-bg-light border">{{ $role->users_count }}</span>
                                </td>
                                <td>
                                    @if($role->isSystem())
                                        <span class="badge text-bg-secondary">Sistema</span>
                                    @else
                                        <span class="badge text-bg-info">Personalizado</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        <a href="{{ route('roles.show', $role) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                                        @can('update', $role)
                                            <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                                        @endcan
                                        @can('duplicate', $role)
                                            <a href="{{ route('roles.duplicate-form', $role) }}" class="btn btn-sm btn-outline-secondary">Duplicar</a>
                                        @endcan
                                        @can('delete', $role)
                                            <a href="{{ route('roles.delete-form', $role) }}" class="btn btn-sm btn-outline-danger">Eliminar</a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-secondary py-4">No hay roles registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
