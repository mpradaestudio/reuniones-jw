<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión · {{ config('app.name', 'Reuniones JW') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex h-full items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-gray-800">Reuniones JW</h1>
            <p class="text-sm text-gray-500">Gestión de congregaciones</p>
            @isset($currentCongregation)
                <p class="mt-2 inline-block rounded-full bg-slate-800 px-3 py-1 text-xs font-medium text-white">
                    {{ $currentCongregation->nombre }}
                </p>
            @endisset
        </div>

        <div class="rounded-xl bg-white p-8 shadow-sm">
            <h2 class="mb-6 text-lg font-semibold text-gray-800">Iniciar sesión</h2>

            @if ($errors->any())
                <div class="mb-4 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Correo electrónico</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                           class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                    <input id="password" name="password" type="password" required
                           class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500">
                </div>

                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox"
                           class="h-4 w-4 rounded border-gray-300 text-slate-700 focus:ring-slate-500">
                    <label for="remember" class="ml-2 text-sm text-gray-600">Recordarme</label>
                </div>

                <button type="submit"
                        class="w-full rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
                    Entrar
                </button>
            </form>
        </div>

        <p class="mt-4 text-center text-xs text-gray-400">
            &copy; {{ date('Y') }} Reuniones JW
        </p>
    </div>
</body>
</html>
