@extends('layouts.app')

@section('title', 'Eliminar rol')

@section('content')
    <div class="mb-3">
        <a href="{{ route('roles.index') }}" class="text-decoration-none small text-secondary">&larr; Volver al listado</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h2 class="h5 text-dark mb-1">Eliminar rol «{{ $role->name }}»</h2>

            @if ($errors->any())
                <div class="alert alert-danger mt-3" role="alert">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('roles.destroy', $role) }}" class="mt-3">
                @csrf
                @method('DELETE')

                @if($usersCount > 0)
                    <div class="alert alert-warning" role="alert">
                        Este rol tiene <strong>{{ $usersCount }}</strong> usuario(s) asignado(s). Para eliminarlo,
                        selecciona un <strong>rol destino</strong> al que se reasignarán automáticamente
                        (manteniendo un único rol por usuario).
                    </div>

                    <div class="mb-3" style="max-width: 24rem;">
                        <label for="reassign_to" class="form-label">Reasignar usuarios a</label>
                        <select id="reassign_to" name="reassign_to"
                                class="form-select @error('reassign_to') is-invalid @enderror" required>
                            <option value="">Selecciona un rol destino</option>
                            @foreach($targets as $target)
                                <option value="{{ $target->name }}" @selected(old('reassign_to') === $target->name)>
                                    {{ $target->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('reassign_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                @else
                    <p class="text-secondary">Esta acción no se puede deshacer. El rol no tiene usuarios asignados.</p>
                @endif

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-danger">
                        {{ $usersCount > 0 ? 'Reasignar y eliminar' : 'Eliminar rol' }}
                    </button>
                    <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection
