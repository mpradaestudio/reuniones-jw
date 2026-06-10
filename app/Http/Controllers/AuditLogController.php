<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Congregation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Módulo Auditoría (solo lectura).
 *
 * Permite consultar el historial de eventos ya registrados en `audit_logs`.
 * Este módulo NO registra eventos (eso lo hace `App\Support\AuditLogger` desde
 * cada módulo) ni los modifica: expone únicamente consulta (index) y detalle
 * (show).
 *
 * Autorización en profundidad:
 *  - Middleware `permission:audit.view` en la ruta (permiso de Spatie).
 *  - AuditLogPolicy (`viewAny`/`view`) con aislamiento por congregación.
 *
 * Alcance de visibilidad (decisión aprobada):
 *  - SuperAdministrador: ve toda la auditoría (todas las congregaciones y los
 *    eventos globales sin congregación).
 *  - AdministradorCongregacion: solo los registros de su congregación.
 *
 * IP y user agent NO se exponen en el listado; solo en el detalle.
 */
class AuditLogController extends Controller
{
    /**
     * Número de registros por página en el listado.
     */
    private const PER_PAGE = 20;

    /**
     * Listado de eventos de auditoría con filtros y paginación del lado servidor.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', AuditLog::class);

        $actor = $request->user();

        $filters = [
            'desde' => trim((string) $request->query('desde', '')),
            'hasta' => trim((string) $request->query('hasta', '')),
            'event' => trim((string) $request->query('event', '')),
            'autor' => trim((string) $request->query('autor', '')),
            'tipo' => trim((string) $request->query('tipo', '')),
            'congregation' => trim((string) $request->query('congregation', '')),
        ];

        // Solo se exponen las columnas necesarias para el listado: IP y user
        // agent quedan fuera (visibles únicamente en el detalle).
        $query = AuditLog::query()
            ->with(['user:id,nombre,apellidos,email', 'congregation:id,nombre'])
            ->select([
                'id',
                'congregation_id',
                'user_id',
                'event',
                'auditable_type',
                'auditable_id',
                'created_at',
            ]);

        // Aislamiento por congregación: el SuperAdministrador ve todo; el resto,
        // solo su congregación.
        if (! $actor->isSuperAdmin()) {
            $query->where('congregation_id', $actor->congregation_id);
        }

        // Filtro por rango de fechas (sobre created_at).
        if ($filters['desde'] !== '' && $this->isValidDate($filters['desde'])) {
            $query->whereDate('created_at', '>=', $filters['desde']);
        }
        if ($filters['hasta'] !== '' && $this->isValidDate($filters['hasta'])) {
            $query->whereDate('created_at', '<=', $filters['hasta']);
        }

        // Filtro por evento (acción).
        if ($filters['event'] !== '') {
            $query->where('event', $filters['event']);
        }

        // Filtro por autor (id de usuario).
        if ($filters['autor'] !== '' && ctype_digit($filters['autor'])) {
            $query->where('user_id', (int) $filters['autor']);
        }

        // Filtro por tipo de entidad afectada (auditable_type).
        if ($filters['tipo'] !== '') {
            $query->where('auditable_type', $filters['tipo']);
        }

        // Filtro por congregación: solo el SuperAdministrador puede acotar por
        // otra congregación. Para el resto, el aislamiento ya está aplicado.
        if ($actor->isSuperAdmin() && $filters['congregation'] !== '' && ctype_digit($filters['congregation'])) {
            $query->where('congregation_id', (int) $filters['congregation']);
        }

        $logs = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('audit.index', [
            'logs' => $logs,
            'filters' => $filters,
            'events' => $this->availableEvents($actor),
            'auditableTypes' => $this->availableAuditableTypes($actor),
            'congregations' => $actor->isSuperAdmin()
                ? Congregation::query()->orderBy('nombre')->get(['id', 'nombre'])
                : collect(),
            'isSuperAdmin' => $actor->isSuperAdmin(),
        ]);
    }

    /**
     * Detalle de un evento de auditoría (incluye IP y user agent).
     */
    public function show(Request $request, AuditLog $auditLog): View
    {
        $this->authorize('view', $auditLog);

        $auditLog->load(['user:id,nombre,apellidos,email', 'congregation:id,nombre']);

        return view('audit.show', [
            'log' => $auditLog,
        ]);
    }

    /**
     * Eventos distintos disponibles para el filtro, respetando el aislamiento
     * por congregación.
     *
     * @return \Illuminate\Support\Collection<int, string>
     */
    private function availableEvents(User $actor): Collection
    {
        return AuditLog::query()
            ->when(! $actor->isSuperAdmin(), fn ($q) => $q->where('congregation_id', $actor->congregation_id))
            ->select('event')
            ->distinct()
            ->orderBy('event')
            ->pluck('event');
    }

    /**
     * Tipos de entidad auditada disponibles para el filtro, respetando el
     * aislamiento por congregación.
     *
     * @return \Illuminate\Support\Collection<int, string>
     */
    private function availableAuditableTypes(User $actor): Collection
    {
        return AuditLog::query()
            ->when(! $actor->isSuperAdmin(), fn ($q) => $q->where('congregation_id', $actor->congregation_id))
            ->whereNotNull('auditable_type')
            ->select('auditable_type')
            ->distinct()
            ->orderBy('auditable_type')
            ->pluck('auditable_type');
    }

    /**
     * Valida que una cadena tenga formato de fecha `Y-m-d`.
     */
    private function isValidDate(string $value): bool
    {
        $date = \DateTime::createFromFormat('Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value;
    }
}
