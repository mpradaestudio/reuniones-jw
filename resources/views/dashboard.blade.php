@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-heading', 'Dashboard')

@section('content')
    @php($cards = [
        ['label' => 'Total congregaciones', 'value' => $metrics['congregations_total'], 'icon' => 'bi-building', 'bg' => 'bg-primary'],
        ['label' => 'Congregaciones activas', 'value' => $metrics['congregations_active'], 'icon' => 'bi-building-check', 'bg' => 'bg-success'],
        ['label' => 'Total usuarios', 'value' => $metrics['users_total'], 'icon' => 'bi-people', 'bg' => 'bg-info'],
        ['label' => 'Usuarios activos', 'value' => $metrics['users_active'], 'icon' => 'bi-person-check', 'bg' => 'bg-warning'],
    ])

    <div class="row g-3">
        @foreach($cards as $card)
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 rjw-stat-card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="rjw-stat-icon {{ $card['bg'] }}">
                            <i class="bi {{ $card['icon'] }}"></i>
                        </span>
                        <div>
                            <div class="text-muted small">{{ $card['label'] }}</div>
                            <div class="rjw-stat-value">{{ $card['value'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

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
