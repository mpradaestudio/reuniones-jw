@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-3">
                <span class="d-inline-flex align-items-center justify-content-center rounded bg-body-secondary text-secondary fs-5"
                      style="width: 40px; height: 40px;" aria-hidden="true">&#9432;</span>
                <h2 class="h5 mb-0 text-dark">{{ $title }}</h2>
            </div>
            <p class="mt-3 mb-0 small text-secondary">{{ $description }}</p>
            <span class="badge text-bg-warning mt-3">En construcción</span>
        </div>
    </div>
@endsection
