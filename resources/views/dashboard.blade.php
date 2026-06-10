@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-heading', 'Dashboard')

@section('content')
    @php($canViewCongregations = auth()->user()->can('congregations.view'))
    @php($cards = [
        ['label' => 'Total congregaciones', 'value' => $metrics['congregations_total'], 'icon' => 'bi-building', 'bg' => 'bg-primary', 'route' => $canViewCongregations ? 'congregations.index' : null],
        ['label' => 'Congregaciones activas', 'value' => $metrics['congregations_active'], 'icon' => 'bi-building-check', 'bg' => 'bg-success', 'route' => $canViewCongregations ? 'congregations.index' : null],
        ['label' => 'Total usuarios', 'value' => $metrics['users_total'], 'icon' => 'bi-people', 'bg' => 'bg-info', 'route' => null],
        ['label' => 'Usuarios activos', 'value' => $metrics['users_active'], 'icon' => 'bi-person-check', 'bg' => 'bg-warning', 'route' => null],
    ])

    <div class="row g-3">
        @foreach($cards as $card)
            <div class="col-12 col-sm-6 col-xl-3">
                @php($cardLink = $card['route'] ?? null)
                <{{ $cardLink ? 'a' : 'div' }}
                    @if($cardLink) href="{{ route($cardLink) }}" @endif
                    class="card border-0 shadow-sm rounded-4 rjw-stat-card h-100 text-decoration-none text-reset {{ $cardLink ? 'rjw-stat-link' : '' }}">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="rjw-stat-icon {{ $card['bg'] }}">
                            <i class="bi {{ $card['icon'] }}"></i>
                        </span>
                        <div>
                            <div class="text-muted small">{{ $card['label'] }}</div>
                            <div class="rjw-stat-value">{{ $card['value'] }}</div>
                        </div>
                    </div>
                </{{ $cardLink ? 'a' : 'div' }}>
            </div>
        @endforeach
    </div>

    @can('congregations.view')
        <div class="card border-0 shadow-sm rounded-4 mt-4">
            <div class="card-header bg-white border-0 d-flex align-items-center justify-content-between py-3">
                <h2 class="h6 fw-semibold mb-0">Últimas congregaciones</h2>
                <a href="{{ route('congregations.index') }}" class="btn btn-sm btn-outline-secondary">Ver todas</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Subdominio</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php($badge = ['active' => 'success', 'inactive' => 'secondary', 'suspended' => 'warning'])
                        @forelse($latestCongregations as $congregation)
                            <tr>
                                <td class="fw-medium">{{ $congregation->nombre }}</td>
                                <td><code>{{ $congregation->subdominio }}</code></td>
                                <td>
                                    <span class="badge text-bg-{{ $badge[$congregation->estado->value] ?? 'secondary' }}">
                                        {{ $congregation->estado->label() }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">Sin congregaciones.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endcan

    <div class="card border-0 shadow-sm rounded-4 mt-4">
        <div class="card-body">
            <h2 class="h6 fw-semibold mb-2">Bienvenido</h2>
            <p class="text-muted mb-0">
                Este es el panel inicial de <strong>Reuniones JW</strong>. Los módulos de
                gestión (Congregaciones, Usuarios, Roles) se irán habilitando conforme se
                implementen sus funcionalidades.
            </p>
        </div>
    </div>
@endsection
