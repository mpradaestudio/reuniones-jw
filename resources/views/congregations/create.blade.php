@extends('layouts.app')

@section('title', 'Nueva congregación')
@section('page-heading', 'Nueva congregación')

@section('content')
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4" style="max-width: 36rem;">
            <form method="POST" action="{{ route('congregations.store') }}">
                @include('congregations._form', ['submitLabel' => 'Crear congregación'])
            </form>
        </div>
    </div>
@endsection
