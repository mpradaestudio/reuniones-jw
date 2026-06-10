<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión · {{ config('app.name', 'Reuniones JW') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body { font-family: "Google Sans Flex", "Google Sans", "Product Sans", system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; }
    </style>
</head>
<body class="bg-light">
<div class="d-flex align-items-center justify-content-center min-vh-100 p-3">
    <div class="w-100" style="max-width: 28rem;">
        <div class="text-center mb-4">
            <h1 class="h3 fw-bold text-dark mb-1">Reuniones JW</h1>
            <p class="small text-secondary mb-0">Gestión de congregaciones</p>
            @isset($currentCongregation)
                <span class="badge bg-dark mt-2">{{ $currentCongregation->nombre }}</span>
            @endisset
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h2 class="h5 fw-semibold text-dark mb-4">Iniciar sesión</h2>

                @if ($errors->any())
                    <div class="alert alert-danger py-2 small" role="alert">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Correo electrónico</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                               class="form-control @error('email') is-invalid @enderror">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input id="password" name="password" type="password" required class="form-control">
                    </div>

                    <div class="form-check mb-3">
                        <input id="remember" name="remember" type="checkbox" class="form-check-input">
                        <label for="remember" class="form-check-label small text-secondary">Recordarme</label>
                    </div>

                    <button type="submit" class="btn btn-dark w-100">Entrar</button>
                </form>
            </div>
        </div>

        <p class="text-center text-secondary mt-3" style="font-size: .75rem;">
            &copy; {{ date('Y') }} Reuniones JW
        </p>
    </div>
</div>
</body>
</html>
