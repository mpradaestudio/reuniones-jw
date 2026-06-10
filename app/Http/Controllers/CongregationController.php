<?php

namespace App\Http\Controllers;

use App\Enums\CongregationStatus;
use App\Http\Requests\Congregation\StoreCongregationRequest;
use App\Http\Requests\Congregation\UpdateCongregationRequest;
use App\Http\Requests\Congregation\UpdateCongregationStatusRequest;
use App\Models\Congregation;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CongregationController extends Controller
{
    /**
     * Listado con búsqueda y filtros (estado / archivadas).
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Congregation::class);

        $search = trim((string) $request->query('q', ''));
        $estado = $request->query('estado');
        $trashed = $request->boolean('archivadas');

        $query = Congregation::query()
            ->when($trashed, fn ($q) => $q->onlyTrashed())
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('nombre', 'like', "%{$search}%")
                        ->orWhere('subdominio', 'like', "%{$search}%");
                });
            })
            ->when(
                $estado && CongregationStatus::tryFrom($estado),
                fn ($q) => $q->where('estado', $estado),
            )
            ->orderBy('nombre');

        $congregations = $query->paginate(10)->withQueryString();

        return view('congregations.index', [
            'congregations' => $congregations,
            'search' => $search,
            'estado' => $estado,
            'trashed' => $trashed,
            'statuses' => CongregationStatus::options(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Congregation::class);

        return view('congregations.create', [
            'statuses' => CongregationStatus::options(),
        ]);
    }

    public function store(StoreCongregationRequest $request): RedirectResponse
    {
        $congregation = Congregation::create($request->validated());

        AuditLogger::record('created', $congregation, [], $congregation->only(['nombre', 'subdominio', 'estado']));

        return redirect()
            ->route('congregations.index')
            ->with('status', "Congregación «{$congregation->nombre}» creada correctamente.");
    }

    public function edit(Congregation $congregation): View
    {
        $this->authorize('update', $congregation);

        return view('congregations.edit', [
            'congregation' => $congregation,
            'statuses' => CongregationStatus::options(),
        ]);
    }

    public function update(UpdateCongregationRequest $request, Congregation $congregation): RedirectResponse
    {
        $old = $congregation->only(['nombre', 'subdominio', 'estado']);

        $congregation->update($request->validated());

        AuditLogger::record('updated', $congregation, $old, $congregation->only(['nombre', 'subdominio', 'estado']));

        return redirect()
            ->route('congregations.index')
            ->with('status', "Congregación «{$congregation->nombre}» actualizada.");
    }

    /**
     * Cambia el estado: activar / inactivar / suspender.
     */
    public function updateStatus(UpdateCongregationStatusRequest $request, Congregation $congregation): RedirectResponse
    {
        $this->authorize('toggleStatus', $congregation);

        $old = ['estado' => $congregation->estado->value];
        $congregation->update(['estado' => $request->validated()['estado']]);

        AuditLogger::record('status_changed', $congregation, $old, ['estado' => $congregation->estado->value]);

        return redirect()
            ->route('congregations.index')
            ->with('status', "Estado de «{$congregation->nombre}» actualizado a {$congregation->estado->label()}.");
    }

    /**
     * Borrado lógico (SoftDelete): envía a la papelera, no borra físicamente.
     */
    public function destroy(Congregation $congregation): RedirectResponse
    {
        $this->authorize('delete', $congregation);

        $congregation->delete();

        AuditLogger::record('deleted', $congregation);

        return redirect()
            ->route('congregations.index')
            ->with('status', "Congregación «{$congregation->nombre}» archivada.");
    }

    /**
     * Restaura una congregación archivada.
     */
    public function restore(Congregation $congregation): RedirectResponse
    {
        $this->authorize('restore', $congregation);

        $congregation->restore();

        AuditLogger::record('restored', $congregation);

        return redirect()
            ->route('congregations.index', ['archivadas' => 1])
            ->with('status', "Congregación «{$congregation->nombre}» restaurada.");
    }
}
