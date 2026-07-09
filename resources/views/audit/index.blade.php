@extends('layouts.app')

@section('title', 'Auditoría')

@section('content')
    @php
        /**
         * Clase de badge Bootstrap 5 según la acción del evento (parte tras el punto).
         * Ej.: user.created -> created -> éxito; role.deleted -> deleted -> peligro.
         */
        $eventBadgeClass = function (string $event): string {
            $action = \Illuminate\Support\Str::afterLast($event, '.');

            return match ($action) {
                'created', 'restored' => 'text-bg-success',
                'updated' => 'text-bg-primary',
                'duplicated' => 'text-bg-info',
                'status_changed', 'password_reset' => 'text-bg-warning',
                'deleted' => 'text-bg-danger',
                default => 'text-bg-secondary',
            };
        };
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 mb-0 text-dark">Auditoría</h2>
        <span class="text-secondary small">Historial de eventos (solo lectura)</span>
    </div>

    {{-- Filtros --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('audit.index') }}" class="row g-2 align-items-end">
                <div class="col-6 col-md-2">
                    <label for="desde" class="form-label small text-secondary mb-1">Desde</label>
                    <input type="date" id="desde" name="desde" value="{{ $filters['desde'] }}" class="form-control">
                </div>

                <div class="col-6 col-md-2">
                    <label for="hasta" class="form-label small text-secondary mb-1">Hasta</label>
                    <input type="date" id="hasta" name="hasta" value="{{ $filters['hasta'] }}" class="form-control">
                </div>

                <div class="col-12 col-md-3">
                    <label for="event" class="form-label small text-secondary mb-1">Evento</label>
                    <select id="event" name="event" class="form-select">
                        <option value="">Todos</option>
                        @foreach($events as $event)
                            <option value="{{ $event }}" @selected($filters['event'] === $event)>{{ $event }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <label for="tipo" class="form-label small text-secondary mb-1">Tipo de entidad</label>
                    <select id="tipo" name="tipo" class="form-select">
                        <option value="">Todas</option>
                        @foreach($auditableTypes as $type)
                            <option value="{{ $type }}" @selected($filters['tipo'] === $type)>{{ class_basename($type) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label for="autor" class="form-label small text-secondary mb-1">Autor (ID)</label>
                    <input type="number" min="1" id="autor" name="autor" value="{{ $filters['autor'] }}"
                           class="form-control" placeholder="ID de usuario">
                </div>

                @if($isSuperAdmin)
                    <div class="col-6 col-md-3">
                        <label for="congregation" class="form-label small text-secondary mb-1">Congregación</label>
                        <select id="congregation" name="congregation" class="form-select">
                            <option value="">Todas</option>
                            @foreach($congregations as $congregation)
                                <option value="{{ $congregation->id }}"
                                    @selected($filters['congregation'] !== '' && (int) $filters['congregation'] === $congregation->id)>
                                    {{ $congregation->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-dark flex-grow-1">Filtrar</button>
                    <a href="{{ route('audit.index') }}" class="btn btn-outline-secondary">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Resultados --}}
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h6 mb-0 text-dark">Resultados</h2>
                <span class="text-secondary small">{{ $logs->total() }} registro(s)</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Fecha y hora</th>
                            <th scope="col">Autor</th>
                            <th scope="col">Evento</th>
                            <th scope="col">Entidad afectada</th>
                            @if($isSuperAdmin)
                                <th scope="col">Congregación</th>
                            @endif
                            <th scope="col" class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td class="text-secondary small">{{ $log->created_at?->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if($log->user)
                                        <span class="fw-medium text-dark">{{ $log->user->nombre_completo }}</span>
                                        <div class="text-secondary small">{{ $log->user->email }}</div>
                                    @else
                                        <span class="text-secondary small">Sistema</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $eventBadgeClass($log->event) }}">{{ $log->event }}</span>
                                </td>
                                <td class="text-secondary">
                                    @if($log->auditable_type)
                                        {{ class_basename($log->auditable_type) }}
                                        @if($log->auditable_id)
                                            <span class="text-secondary small">#{{ $log->auditable_id }}</span>
                                        @endif
                                    @else
                                        <span class="text-secondary small">—</span>
                                    @endif
                                </td>
                                @if($isSuperAdmin)
                                    <td class="text-secondary">{{ $log->congregation?->nombre ?? 'Global' }}</td>
                                @endif
                                <td class="text-end">
                                    <a href="{{ route('audit.show', $log) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isSuperAdmin ? 6 : 5 }}" class="text-center text-secondary py-4">
                                    No hay registros de auditoría con los criterios indicados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="mt-3">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
