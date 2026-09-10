@extends('plantilla')
@section('contenido')

@section('navbar-title', 'Consulta resultado')
@section('back-route', route('inicio'))

@include('layouts.navbar')

<div class="container mt-4 mb-5">
    <div class="card recepcion-card">
        <div class="card-body p-4">
            <h1>Consulta resultado</h1>
        </div>
    </div>
</div>

@endsection