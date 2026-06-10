@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php($cards = [
        ['label' => 'Total congregaciones', 'value' => $metrics['congregations_total'], 'accent' => 'bg-blue-500'],
        ['label' => 'Congregaciones activas', 'value' => $metrics['congregations_active'], 'accent' => 'bg-emerald-500'],
        ['label' => 'Total usuarios', 'value' => $metrics['users_total'], 'accent' => 'bg-indigo-500'],
        ['label' => 'Usuarios activos', 'value' => $metrics['users_active'], 'accent' => 'bg-amber-500'],
    ])

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($cards as $card)
            <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                <div class="flex items-center gap-4 p-5">
                    <span class="inline-flex h-12 w-12 flex-none items-center justify-center rounded-lg {{ $card['accent'] }} text-lg font-bold text-white">
                        {{ $card['value'] }}
                    </span>
                    <div>
                        <p class="text-sm text-gray-500">{{ $card['label'] }}</p>
                        <p class="text-2xl font-semibold text-gray-800">{{ $card['value'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-8 rounded-xl bg-white p-6 shadow-sm">
        <h2 class="text-base font-semibold text-gray-800">Bienvenido</h2>
        <p class="mt-2 text-sm text-gray-600">
            Este es el panel inicial de <strong>Reuniones JW</strong>. Los módulos de
            gestión (Congregaciones, Usuarios, Roles) se irán habilitando conforme se
            implementen sus funcionalidades.
        </p>
    </div>
@endsection
