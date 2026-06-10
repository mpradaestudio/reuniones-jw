@extends('layouts.app')

@section('title', 'Crear usuario')

@section('content')
    <div class="mb-3">
        <a href="{{ route('users.index') }}" class="text-decoration-none small text-secondary">&larr; Volver al listado</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h2 class="h5 text-dark mb-4">Nuevo usuario</h2>

            @include('users._form', [
                'action' => route('users.store'),
                'method' => 'POST',
                'user' => null,
            ])
        </div>
    </div>
@endsection
