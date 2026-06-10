{{--
    PLACEHOLDER TEMPORAL — Módulo Auditoría (PR A: backend).

    Vista mínima sin estilos cuyo único objetivo es que la ruta `audit.index`
    sea funcional y testeable en esta fase. La pantalla real (Bootstrap 5 +
    Google Sans Flex: barra de filtros, tabla responsive, badges y paginación)
    se implementará en el PR B (UI listado).

    Nota: el listado NO muestra IP ni user agent (solo el detalle, PR C).
--}}
<h1>Auditoría</h1>

<p>Registros: {{ $logs->total() }}</p>

<ul>
    @forelse ($logs as $log)
        <li>
            <a href="{{ route('audit.show', $log) }}">#{{ $log->id }}</a>
            — {{ $log->event }}
            — autor: {{ $log->user?->email ?? 'sistema' }}
            — congregación: {{ $log->congregation?->nombre ?? 'global' }}
            — {{ $log->created_at }}
        </li>
    @empty
        <li>Sin registros para los filtros aplicados.</li>
    @endforelse
</ul>

{{ $logs->links() }}
