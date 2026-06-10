@extends('layouts.app')

@section('title', 'Editar rol')

@section('content')
    <div class="mb-3">
        <a href="{{ route('roles.index') }}" class="text-decoration-none small text-secondary">&larr; Volver al listado</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h2 class="h5 text-dark mb-4">Editar rol</h2>

            @include('roles._form', [
                'action' => route('roles.update', $role),
                'method' => 'PUT',
            ])
        </div>
    </div>
@endsection
