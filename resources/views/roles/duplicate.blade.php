@extends('layouts.app')

@section('title', 'Duplicar rol')

@section('content')
    <div class="mb-3">
        <a href="{{ route('roles.index') }}" class="text-decoration-none small text-secondary">&larr; Volver al listado</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h2 class="h5 text-dark mb-1">Duplicar rol</h2>
            <p class="text-secondary small mb-4">
                Se creará un rol nuevo con los <strong>{{ $permissionsCount }}</strong> permiso(s) de
                «{{ $role->name }}». Podrás ajustarlo después.
            </p>

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('roles.duplicate', $role) }}">
                @csrf
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label for="name" class="form-label">Nombre del nuevo rol</label>
                        <input type="text" id="name" name="name" maxlength="125"
                               value="{{ old('name', $role->name.' (copia)') }}"
                               class="form-control @error('name') is-invalid @enderror" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="description" class="form-label">Descripción <span class="text-secondary fw-normal">(opcional)</span></label>
                        <input type="text" id="description" name="description" maxlength="255"
                               value="{{ old('description', $role->description) }}"
                               class="form-control @error('description') is-invalid @enderror">
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-dark">Duplicar rol</button>
                    <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection
