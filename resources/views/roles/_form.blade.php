{{--
    Formulario compartido de alta/edición de rol.
    Variables:
      - $action (string), $method ('POST'|'PUT')
      - $role (Role|null), $rolePermissions (array<string>)
      - $permissionGroups (array<string, array<string>>)  módulo => [permisos]
      - $isSuperAdminRole (bool)  el rol SuperAdministrador bloquea sus permisos
--}}
@php($role = $role ?? null)
@php($rolePermissions = $rolePermissions ?? [])
@php($isSuperAdminRole = $isSuperAdminRole ?? false)
@php($isEdit = $role !== null)
@php($isSystem = $isEdit && $role->isSystem())

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

<form method="POST" action="{{ $action }}">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif

    <div class="row g-3">
        <div class="col-12 col-md-6">
            <label for="name" class="form-label">Nombre del rol</label>
            <input type="text" id="name" name="name" maxlength="125"
                   value="{{ old('name', $role?->name) }}"
                   class="form-control @error('name') is-invalid @enderror"
                   @if($isSystem) readonly @endif required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            @if($isSystem)
                <div class="form-text">El nombre de un rol de sistema no se puede modificar.</div>
            @endif
        </div>

        <div class="col-12 col-md-6">
            <label for="description" class="form-label">Descripción <span class="text-secondary fw-normal">(opcional)</span></label>
            <input type="text" id="description" name="description" maxlength="255"
                   value="{{ old('description', $role?->description) }}"
                   class="form-control @error('description') is-invalid @enderror">
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <hr class="my-4">

    <div class="d-flex justify-content-between align-items-center mb-2">
        <h3 class="h6 text-dark mb-0">Permisos</h3>
    </div>

    @if($isSuperAdminRole)
        <div class="alert alert-info" role="alert">
            El rol <strong>SuperAdministrador</strong> conserva siempre <strong>todos</strong> los permisos; no es editable.
        </div>
    @endif

    <div class="row g-3">
        @foreach($permissionGroups as $module => $permissions)
            <div class="col-12 col-md-6">
                <div class="card border h-100">
                    <div class="card-body">
                        <p class="text-uppercase text-secondary small fw-semibold mb-2">{{ $module }}</p>
                        @foreach($permissions as $permission)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="permissions[]" value="{{ $permission }}"
                                       id="perm_{{ $permission }}"
                                       @checked(in_array($permission, old('permissions', $rolePermissions), true) || $isSuperAdminRole)
                                       @disabled($isSuperAdminRole)>
                                <label class="form-check-label small" for="perm_{{ $permission }}">{{ $permission }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-dark">{{ $isEdit ? 'Guardar cambios' : 'Crear rol' }}</button>
        <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
