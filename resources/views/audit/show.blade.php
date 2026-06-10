{{--
    PLACEHOLDER TEMPORAL — Módulo Auditoría (PR A: backend).

    Vista mínima sin estilos cuyo único objetivo es que la ruta `audit.show`
    sea funcional y testeable en esta fase. La pantalla real (Bootstrap 5,
    ficha del evento con diff antes/después) se implementará en el PR C.

    A diferencia del listado, el detalle SÍ muestra IP y user agent
    (decisión aprobada).
--}}
<h1>Auditoría — registro #{{ $log->id }}</h1>

<dl>
    <dt>Evento</dt>
    <dd>{{ $log->event }}</dd>

    <dt>Autor</dt>
    <dd>{{ $log->user?->email ?? 'sistema' }}</dd>

    <dt>Congregación</dt>
    <dd>{{ $log->congregation?->nombre ?? 'global' }}</dd>

    <dt>Entidad afectada</dt>
    <dd>{{ $log->auditable_type ?? '—' }} {{ $log->auditable_id ? '#'.$log->auditable_id : '' }}</dd>

    <dt>Fecha</dt>
    <dd>{{ $log->created_at }}</dd>

    <dt>IP</dt>
    <dd>{{ $log->ip_address ?? '—' }}</dd>

    <dt>User agent</dt>
    <dd>{{ $log->user_agent ?? '—' }}</dd>

    <dt>Valores anteriores</dt>
    <dd><pre>{{ $log->old_values ? json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '—' }}</pre></dd>

    <dt>Valores nuevos</dt>
    <dd><pre>{{ $log->new_values ? json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '—' }}</pre></dd>
</dl>

<p><a href="{{ route('audit.index') }}">Volver al listado</a></p>
