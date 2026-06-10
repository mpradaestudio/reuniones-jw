{{--
    Formulario compartido de alta/edición de usuario.
    Variables esperadas:
      - $action       (string)  URL destino del formulario.
      - $method       (string)  'POST' (alta) o 'PUT' (edición).
      - $user         (User|null) Usuario en edición (null en alta).
      - $roles        (Collection<string>) Roles asignables.
      - $statuses     (array<string,string>) [valor => etiqueta].
      - $congregations (Collection) Solo para SuperAdministrador.
      - $isSuperAdmin (bool)
      - $currentRole  (string|null) Rol actual (en edición).
--}}
@php($user = $user ?? null)
@php($currentRole = $currentRole ?? null)
@php($isEdit = $user !== null)

@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <p class="fw-medium mb-1">Revisa los siguientes errores:</p>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ $action }}" novalidate>
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif

    <div class="row g-3">
        <div class="col-12 col-md-6">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" id="nombre" name="nombre" maxlength="100" required
                   value="{{ old('nombre', $user?->nombre) }}"
                   class="form-control @error('nombre') is-invalid @enderror">
            @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-12 col-md-6">
            <label for="apellidos" class="form-label">Apellidos</label>
            <input type="text" id="apellidos" name="apellidos" maxlength="150" required
                   value="{{ old('apellidos', $user?->apellidos) }}"
                   class="form-control @error('apellidos') is-invalid @enderror">
            @error('apellidos')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-12">
            <label for="email" class="form-label">Correo electrónico</label>
            <input type="email" id="email" name="email" maxlength="190" required
                   value="{{ old('email', $user?->email) }}"
                   class="form-control @error('email') is-invalid @enderror">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">El correo debe ser único en toda la plataforma.</div>
        </div>

        <div class="col-12 col-md-6">
            <label for="password" class="form-label">
                Contraseña @if($isEdit)<span class="text-secondary fw-normal">(opcional)</span>@endif
            </label>
            <input type="password" id="password" name="password" autocomplete="new-password"
                   @if(! $isEdit) required @endif
                   class="form-control @error('password') is-invalid @enderror">
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            @if($isEdit)
                <div class="form-text">Déjala en blanco para mantener la actual.</div>
            @endif
        </div>

        <div class="col-12 col-md-6">
            <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
            <input type="password" id="password_confirmation" name="password_confirmation"
                   autocomplete="new-password" @if(! $isEdit) required @endif
                   class="form-control">
        </div>

        <div class="col-12 col-md-6">
            <label for="role" class="form-label">Rol</label>
            <select id="role" name="role" required class="form-select @error('role') is-invalid @enderror">
                <option value="">Selecciona un rol</option>
                @foreach($roles as $roleName)
                    <option value="{{ $roleName }}" @selected(old('role', $currentRole) === $roleName)>{{ $roleName }}</option>
                @endforeach
            </select>
            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">Cada usuario tiene un único rol.</div>
        </div>

        <div class="col-12 col-md-6">
            <label for="estado" class="form-label">Estado</label>
            <select id="estado" name="estado" required class="form-select @error('estado') is-invalid @enderror">
                @foreach($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(old('estado', $user?->estado?->value ?? 'active') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('estado')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        @if($isSuperAdmin)
            <div class="col-12 col-md-6">
                <label for="congregation_id" class="form-label">Congregación</label>
                <select id="congregation_id" name="congregation_id"
                        class="form-select @error('congregation_id') is-invalid @enderror">
                    <option value="">— Sin congregación (global) —</option>
                    @foreach($congregations as $congregation)
                        <option value="{{ $congregation->id }}"
                            @selected((string) old('congregation_id', $user?->congregation_id) === (string) $congregation->id)>
                            {{ $congregation->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('congregation_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">El rol SuperAdministrador no requiere congregación.</div>
            </div>
        @endif
    </div>

    <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-dark">
            {{ $isEdit ? 'Guardar cambios' : 'Crear usuario' }}
        </button>
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
