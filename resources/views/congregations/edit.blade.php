@extends('layouts.app')

@section('title', 'Editar congregación')
@section('page-heading', 'Editar congregación')

@section('content')
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4" style="max-width: 36rem;">
            <form method="POST" action="{{ route('congregations.update', $congregation) }}">
                @method('PUT')
                @include('congregations._form', ['submitLabel' => 'Guardar cambios'])
            </form>
        </div>
    </div>
@endsection
