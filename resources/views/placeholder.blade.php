@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="rounded-xl bg-white p-8 shadow-sm">
        <div class="flex items-center gap-3">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
            <h2 class="text-lg font-semibold text-gray-800">{{ $title }}</h2>
        </div>
        <p class="mt-4 text-sm text-gray-600">{{ $description }}</p>
        <span class="mt-4 inline-block rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-800">
            En construcción
        </span>
    </div>
@endsection
