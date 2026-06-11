@extends('layouts.app')

@section('title', 'Detalle de auditoría')

@section('content')
    @php
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

        $prettyJson = function ($values): ?string {
            if (empty($values)) {
                return null;
            }

            return json_encode($values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        };
    @endphp

    <div class="mb-3">
        <a href="{{ route('audit.index') }}" class="text-decoration-none small text-secondary">&larr; Volver al listado</a>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h2 class="h5 text-dark mb-1">
                        <span class="badge {{ $eventBadgeClass($log->event) }} align-middle">{{ $log->event }}</span>
                        <span class="text-secondary align-middle">Registro #{{ $log->id }}</span>
                    </h2>
                    <p class="text-secondary small mb-0">{{ $log->created_at?->format('d/m/Y H:i:s') }}</p>
                </div>
            </div>

            <hr class="my-3">

            <dl class="row mb-0 small">
                <dt class="col-sm-3 text-secondary">Autor</dt>
                <dd class="col-sm-9 text-dark">
                    @if($log->user)
                        {{ $log->user->nombre_completo }} <span class="text-secondary">({{ $log->user->email }})</span>
                    @else
                        Sistema
                    @endif
                </dd>

                <dt class="col-sm-3 text-secondary">Congregación</dt>
                <dd class="col-sm-9 text-dark">{{ $log->congregation?->nombre ?? 'Global' }}</dd>

                <dt class="col-sm-3 text-secondary">Entidad afectada</dt>
                <dd class="col-sm-9 text-dark">
                    @if($log->auditable_type)
                        {{ class_basename($log->auditable_type) }}{{ $log->auditable_id ? ' #'.$log->auditable_id : '' }}
                    @else
                        —
                    @endif
                </dd>

                <dt class="col-sm-3 text-secondary">Dirección IP</dt>
                <dd class="col-sm-9 text-dark">{{ $log->ip_address ?? '—' }}</dd>

                <dt class="col-sm-3 text-secondary">User-Agent</dt>
                <dd class="col-sm-9 text-dark text-break">{{ $log->user_agent ?? '—' }}</dd>
            </dl>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h3 class="h6 text-dark mb-3">Valores anteriores</h3>
                    @php($old = $prettyJson($log->old_values))
                    @if($old)
                        <pre class="bg-light border rounded p-3 mb-0 small"><code>{{ $old }}</code></pre>
                    @else
                        <p class="text-secondary small mb-0">Sin datos previos.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h3 class="h6 text-dark mb-3">Valores nuevos</h3>
                    @php($new = $prettyJson($log->new_values))
                    @if($new)
                        <pre class="bg-light border rounded p-3 mb-0 small"><code>{{ $new }}</code></pre>
                    @else
                        <p class="text-secondary small mb-0">Sin datos nuevos.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
