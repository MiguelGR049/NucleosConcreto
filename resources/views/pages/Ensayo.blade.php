@extends('plantilla')
@section('contenido')

@section('navbar-title', 'Ensayo')
@section('back-route', route('inicio'))

@include('layouts.navbar')

<div class="container mt-4 mb-5">
    <div class="card recepcion-card">
        <div class="card-body p-4">
            <h1>Ensayo</h1>
        </div>
    </div>
</div>

@endsection