@extends('layouts.app')

@section('title', 'Crear rol')

@section('content')
    <div class="mb-3">
        <a href="{{ route('roles.index') }}" class="text-decoration-none small text-secondary">&larr; Volver al listado</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h2 class="h5 text-dark mb-4">Nuevo rol</h2>

            @include('roles._form', [
                'action' => route('roles.store'),
                'method' => 'POST',
                'role' => null,
            ])
        </div>
    </div>
@endsection
