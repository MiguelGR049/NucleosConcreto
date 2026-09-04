@extends('plantilla')

@section('contenido')

@section('navbar-title', 'Registro de probeta')
@section('back-route', route('Home'))

@include('layouts.navbar')
<div class="container mt-4 mb-5">
    <div class="card recepcion-card">
        <div class="card-body p-4">

            <form action="#" method="POST">
                @csrf

                <div class="row g-3">

                    <div class="col-12">
                        <label class="form-label">Folio</label>
                        <input type="text" class="form-control" value="{{ $folioGenerado }}" readonly>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Clave de obra</label>
                        <select name="clave_obra" class="form-select">
                            <option value="" selected disabled>Seleccione...</option>
                            @foreach($obras as $obra)
                            <option value="{{ $obra->id }}">{{ $obra->clave }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">No. Especímenes</label>
                        <select name="num_especimenes" class="form-select">
                            <option value="" selected disabled>--</option>
                            @for($i = 1; $i <= 10; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Fecha de recepción</label>
                        <input type="date" name="fecha_recepcion" class="form-control">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Elemento</label>
                        <select name="elemento" class="form-select">
                            <option value="" selected disabled>Seleccione...</option>
                            @foreach($elementos as $elemento)
                            <option value="{{ $elemento->id }}">{{ $elemento->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Localización</label>
                        <input type="text" name="localizacion" class="form-control" placeholder="Ej. Eje 3, Nivel 2">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Fecha de muestreo</label>
                        <input type="date" name="fecha_muestreo" class="form-control">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Datos de proyecto</label>
                        <textarea name="datos_proyecto" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Defectos del espécimen</label>
                        <textarea name="defectos_especimen" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label d-block">Fechas de ensayo</label>
                        <div class="d-flex flex-wrap gap-3">
                            @foreach([1, 3, 7, 14, 28] as $dia)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="fechas_ensayo[]" value="{{ $dia }}" id="ensayo{{ $dia }}">
                                <label class="form-check-label" for="ensayo{{ $dia }}">
                                    {{ $dia }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Envía</label>
                        <input type="text" name="envia" class="form-control" placeholder="Nombre de quien envía">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Recibe</label>
                        <input type="text" name="recibe" class="form-control" placeholder="Nombre de quien recibe">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label text-muted">Usuario que registra</label>
                        <input type="text" class="form-control" value="{{ $usuario->usuario ?? 'N/A' }}" disabled>
                    </div>

                    <div class="col-12">
                        <label class="form-label text-muted">Fecha de registro</label>
                        <input type="text" class="form-control" value="{{ date('d/m/Y H:i') }}" disabled>
                    </div>

                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-guardar">Guardar Recepción</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection