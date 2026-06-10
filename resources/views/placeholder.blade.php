@extends('layouts.app')

@section('title', $title)
@section('page-heading', $title)

@section('content')
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-3 mb-3">
                <span class="rjw-stat-icon bg-secondary">
                    <i class="bi bi-cone-striped"></i>
                </span>
                <h2 class="h5 fw-semibold mb-0">{{ $title }}</h2>
            </div>
            <p class="text-muted">{{ $description }}</p>
            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                En construcción
            </span>
        </div>
    </div>
@endsection
