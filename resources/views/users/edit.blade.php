@extends('layouts.app')

@section('title', 'Editar usuario')

@section('content')
    <div class="mb-3">
        <a href="{{ route('users.index') }}" class="text-decoration-none small text-secondary">&larr; Volver al listado</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h2 class="h5 text-dark mb-1">Editar usuario</h2>
            <p class="text-secondary small mb-4">{{ $user->nombre_completo }} · {{ $user->email }}</p>

            @include('users._form', [
                'action' => route('users.update', $user),
                'method' => 'PUT',
            ])
        </div>
    </div>
@endsection
