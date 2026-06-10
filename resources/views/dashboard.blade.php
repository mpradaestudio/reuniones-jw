@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php($cards = [
        ['label' => 'Total congregaciones', 'value' => $metrics['congregations_total'], 'accent' => 'text-bg-primary'],
        ['label' => 'Congregaciones activas', 'value' => $metrics['congregations_active'], 'accent' => 'text-bg-success'],
        ['label' => 'Total usuarios', 'value' => $metrics['users_total'], 'accent' => 'text-bg-info'],
        ['label' => 'Usuarios activos', 'value' => $metrics['users_active'], 'accent' => 'text-bg-warning'],
    ])

    <div class="row g-3">
        @foreach($cards as $card)
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="d-inline-flex align-items-center justify-content-center rounded {{ $card['accent'] }} fw-bold"
                              style="width: 48px; height: 48px;">
                            {{ $card['value'] }}
                        </span>
                        <div>
                            <p class="mb-0 small text-secondary">{{ $card['label'] }}</p>
                            <p class="mb-0 fs-4 fw-semibold text-dark">{{ $card['value'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card shadow-sm border-0 mt-4">
        <div class="card-body">
            <h2 class="h6 fw-semibold text-dark">Bienvenido</h2>
            <p class="mb-0 small text-secondary">
                Este es el panel inicial de <strong>Reuniones JW</strong>. Los módulos de
                gestión (Congregaciones, Usuarios, Roles) se irán habilitando conforme se
                implementen sus funcionalidades.
            </p>
        </div>
    </div>
@endsection
