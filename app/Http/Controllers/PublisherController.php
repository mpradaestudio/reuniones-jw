<?php

namespace App\Http\Controllers;

use App\Enums\PublisherPrivilege;
use App\Enums\PublisherStatus;
use App\Http\Requests\Publishers\StorePublisherRequest;
use App\Http\Requests\Publishers\TogglePublisherStatusRequest;
use App\Http\Requests\Publishers\UpdatePublisherRequest;
use App\Models\Publisher;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Módulo Publicadores.
 *
 * PR A: acciones de escritura (store/update/toggleStatus/delete) y placeholder
 * de listado. La UI completa (index/create/edit views en Bootstrap 5) llega en PR B/C.
 *
 * Autorización en profundidad:
 *  - Middleware `permission:publishers.*` en la ruta (Spatie).
 *  - PublisherPolicy (permiso + misma congregación) en Form Requests / authorize().
 *
 * Decisión D — protección del último anciano activo:
 *  Ninguna operación puede dejar a una congregación sin al menos un anciano activo.
 */
class PublisherController extends Controller
{
    /**
     * Placeholder de listado — la UI real llega en PR B.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Publisher::class);

        return view('placeholder', [
            'title'   => 'Publicadores',
            'message' => 'El listado de publicadores estará disponible en la próxima iteración.',
        ]);
    }

    public function store(StorePublisherRequest $request): RedirectResponse
    {
        $data  = $request->validated();
        $actor = $request->user();

        // congregation_id: la del actor para roles no globales.
        if (! $actor->isSuperAdmin()) {
            $data['congregation_id'] = $actor->congregation_id;
        }

        $publisher = DB::transaction(function () use ($data) {
            $publisher = Publisher::create($data);

            AuditLogger::record('publisher.created', $publisher, [], [
                'nombre'      => $publisher->nombre,
                'apellidos'   => $publisher->apellidos,
                'genero'      => $publisher->genero,
                'privilegio'  => $publisher->privilegio->value,
                'estado'      => $publisher->estado->value,
                'es_nombrado' => $publisher->es_nombrado,
                'user_id'     => $publisher->user_id,
            ]);

            return $publisher;
        });

        return redirect()
            ->route('publishers.index')
            ->with('status', "Publicador «{$publisher->nombre_completo}» creado correctamente.");
    }

    public function update(UpdatePublisherRequest $request, Publisher $publisher): RedirectResponse
    {
        $data = $request->validated();

        // Protección del último anciano activo (decisión D):
        // Si el cambio degrada el privilegio o desactiva a un anciano activo,
        // verificar que quede al menos otro anciano activo en la congregación.
        $degradingElder = $publisher->isElder()
            && ($data['privilegio'] !== PublisherPrivilege::Elder->value
                || $data['estado'] !== PublisherStatus::Active->value);

        if ($degradingElder) {
            $this->ensureNotLastActiveElder($publisher);
        }

        DB::transaction(function () use ($data, $publisher) {
            $before = [
                'nombre'      => $publisher->nombre,
                'apellidos'   => $publisher->apellidos,
                'genero'      => $publisher->genero,
                'privilegio'  => $publisher->privilegio->value,
                'estado'      => $publisher->estado->value,
                'es_nombrado' => $publisher->es_nombrado,
                'user_id'     => $publisher->user_id,
                'fecha_bautismo' => $publisher->fecha_bautismo?->toDateString(),
            ];

            $publisher->fill($data);
            $publisher->save();

            $after = [
                'nombre'      => $publisher->nombre,
                'apellidos'   => $publisher->apellidos,
                'genero'      => $publisher->genero,
                'privilegio'  => $publisher->privilegio->value,
                'estado'      => $publisher->estado->value,
                'es_nombrado' => $publisher->es_nombrado,
                'user_id'     => $publisher->user_id,
                'fecha_bautismo' => $publisher->fecha_bautismo?->toDateString(),
            ];

            // Solo auditar los campos que realmente cambiaron.
            $changedOld = [];
            $changedNew = [];
            foreach ($after as $field => $value) {
                if ($before[$field] !== $value) {
                    $changedOld[$field] = $before[$field];
                    $changedNew[$field] = $value;
                }
            }

            AuditLogger::record('publisher.updated', $publisher, $changedOld, $changedNew);
        });

        return redirect()
            ->route('publishers.index')
            ->with('status', "Publicador «{$publisher->nombre_completo}» actualizado correctamente.");
    }

    public function toggleStatus(TogglePublisherStatusRequest $request, Publisher $publisher): RedirectResponse
    {
        $newStatus = PublisherStatus::from($request->validated()['estado']);

        // Protección del último anciano activo (decisión D):
        // Si el anciano activo pasa a irregular o inactivo, proteger el último.
        if ($publisher->isElder()
            && $publisher->isActive()
            && $newStatus !== PublisherStatus::Active
        ) {
            $this->ensureNotLastActiveElder($publisher);
        }

        DB::transaction(function () use ($publisher, $newStatus) {
            $old = $publisher->estado->value;
            $publisher->estado = $newStatus;
            $publisher->save();

            AuditLogger::record(
                'publisher.status_changed',
                $publisher,
                ['estado' => $old],
                ['estado' => $publisher->estado->value],
            );
        });

        return redirect()
            ->route('publishers.index')
            ->with('status', "Estado de «{$publisher->nombre_completo}» actualizado a {$publisher->estado->label()}.");
    }

    public function destroy(Request $request, Publisher $publisher): RedirectResponse
    {
        $this->authorize('delete', $publisher);

        // Protección del último anciano activo al eliminar.
        if ($publisher->isElder() && $publisher->isActive()) {
            $this->ensureNotLastActiveElder($publisher);
        }

        DB::transaction(function () use ($publisher) {
            AuditLogger::record('publisher.deleted', $publisher, [
                'nombre'     => $publisher->nombre,
                'apellidos'  => $publisher->apellidos,
                'privilegio' => $publisher->privilegio->value,
                'estado'     => $publisher->estado->value,
            ], []);

            $publisher->delete();
        });

        return redirect()
            ->route('publishers.index')
            ->with('status', "Publicador «{$publisher->nombre_completo}» eliminado correctamente.");
    }

    /**
     * Garantiza que haya al menos otro anciano activo en la misma congregación.
     *
     * @throws ValidationException
     */
    protected function ensureNotLastActiveElder(Publisher $publisher): void
    {
        $others = Publisher::withoutGlobalScopes()
            ->where('congregation_id', $publisher->congregation_id)
            ->where('id', '!=', $publisher->id)
            ->where('privilegio', PublisherPrivilege::Elder->value)
            ->where('estado', PublisherStatus::Active->value)
            ->count();

        if ($others === 0) {
            throw ValidationException::withMessages([
                'privilegio' => 'No se puede dejar a la congregación sin al menos un anciano activo.',
            ]);
        }
    }
}
