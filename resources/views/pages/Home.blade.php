@extends('plantilla')

@section('contenido')

@section('navbar-title', 'Ensayos a compresión de núcleos')
@include('layouts.navbar')

<div class="container d-flex flex-column justify-content-center align-items-center">

    <div class="d-flex flex-wrap justify-content-center gap-2 mt-4">
        <a href="{{ route('recepcion') }}" id="recepcion" class="btn d-flex flex-column justify-content-center align-items-center text-decoration-none">
            <i class="fa-solid fa-circle-down mb-1"></i>
            Recepción
        </a>
        <a href="#" id="propiedades" class="btn d-flex flex-column justify-content-center align-items-center text-decoration-none">
            <i class="fa-solid fa-list mb-1"></i>
            Propiedades de ensayo
        </a>
    </div>

    <div class="d-flex flex-wrap justify-content-center gap-2 mt-4">
        <a href="#" id="ensayo" class="btn d-flex flex-column justify-content-center align-items-center text-decoration-none">
            <i class="fa-solid fa-vial mb-1"></i>
            Ensayo
        </a>
        <a href="#" id="consulta" class="btn d-flex flex-column justify-content-center align-items-center text-decoration-none">
            <i class="fa-solid fa-search mb-1"></i>
            Consulta resultado
        </a>
    </div>

</div>
@endsection