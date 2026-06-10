@csrf

<div class="mb-3">
    <label for="nombre" class="form-label">Nombre</label>
    <input type="text" id="nombre" name="nombre"
           class="form-control @error('nombre') is-invalid @enderror"
           value="{{ old('nombre', $congregation->nombre ?? '') }}" required autofocus>
    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label for="subdominio" class="form-label">Subdominio</label>
    <div class="input-group">
        <input type="text" id="subdominio" name="subdominio"
               class="form-control @error('subdominio') is-invalid @enderror"
               value="{{ old('subdominio', $congregation->subdominio ?? '') }}"
               placeholder="central" required>
        <span class="input-group-text">.{{ config('tenancy.base_domain') }}</span>
        @error('subdominio') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="form-text">Solo minúsculas, números y guiones. Identifica el acceso de la congregación.</div>
</div>

<div class="mb-4">
    <label for="estado" class="form-label">Estado</label>
    <select id="estado" name="estado" class="form-select @error('estado') is-invalid @enderror" required>
        @foreach($statuses as $value => $label)
            <option value="{{ $value }}"
                @selected(old('estado', $congregation->estado->value ?? 'active') === $value)>
                {{ $label }}
            </option>
        @endforeach
    </select>
    @error('estado') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    <a href="{{ route('congregations.index') }}" class="btn btn-outline-secondary">Cancelar</a>
</div>
