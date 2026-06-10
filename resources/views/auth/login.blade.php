@extends('layouts.guest')

@section('title', 'Iniciar sesión')

@section('content')
    <div class="w-100" style="max-width: 26rem;">
        <div class="text-center mb-4 text-white">
            <h1 class="h3 fw-semibold mb-1">Reuniones JW</h1>
            <p class="text-white-50 mb-2">Gestión de congregaciones</p>
            @isset($currentCongregation)
                <span class="badge bg-primary rounded-pill">{{ $currentCongregation->nombre }}</span>
            @endisset
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-sm-5">
                <h2 class="h5 fw-semibold mb-4">Iniciar sesión</h2>

                @if ($errors->any())
                    <div class="alert alert-danger py-2" role="alert">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Correo electrónico</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}"
                               class="form-control" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input id="password" name="password" type="password" class="form-control" required>
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label" for="remember">Recordarme</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2">Entrar</button>
                </form>
            </div>
        </div>

        <p class="text-center text-white-50 small mt-4 mb-0">&copy; {{ date('Y') }} Reuniones JW</p>
    </div>
@endsection
