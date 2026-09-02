@extends('plantilla')

@section('contenido')
<div class="container vh-100 justify-content-center align-items-center mt-5">
    <h1 class="text-center">Ensayos a compresión de núcleos</h1>

    <div class="d-grid gap-2 d-md-block d-flex justify-content-center mt-4">
        <button id="recepcion" class="btn" type="button">
            <i class="fa-solid fa-circle-down"></i>
            Recepción 
        </button>
        <button id="propiedades" class="btn" type="button">
            <i class="fa-solid fa-list"></i>
            Propiedades de ensayo
        </button>
    </div>

    <div class="d-grid gap-2 d-md-block d-flex justify-content-center mt-4">
        <button id="ensayo" class="btn" type="button">
            <i class="fa-solid fa-vial"></i>
            Ensayo
        </button>
        <button id="consulta" class="btn" type="button">
            <i class="fa-solid fa-search"></i>
            Consulta resultado
        </button>
    </div>

</div>
@endsection