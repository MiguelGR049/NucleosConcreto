@extends('plantilla')

@section('contenido')

@section('navbar-title', 'Registro de probeta')
@section('back-route', route('inicio'))

@include('layouts.navbar')

@php
// Campos de cada paso (para abrir el paso correcto si el servidor devuelve errores)
$camposPaso = [
1 => ['folio', 'clave_obra', 'cliente', 'obra', 'atencion_a', 'numero_informe', 'num_especimenes'],
2 => ['especimenes'],
3 => ['fecha_colado', 'fecha_muestreo', 'fecha_recepcion', 'datos_proyecto', 'defectos_especimen'],
4 => ['fechas_ensayo', 'envia', 'recibe', 'observaciones'],
];

$pasoInicial = 1;
if ($errors->any()) {
$pasosConError = [];
foreach ($errors->keys() as $clave) {
$base = explode('.', $clave)[0];
foreach ($camposPaso as $numPaso => $campos) {
if (in_array($base, $campos)) $pasosConError[] = $numPaso;
}
}
$pasoInicial = $pasosConError ? min($pasosConError) : 1;
}
@endphp

<div class="container mt-4 mb-5">
    <div class="card recepcion-card">
        <div class="card-body p-4">

            @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
            @endif

            <form action="{{ route('recepcion.store') }}" method="POST" id="formRecepcion" data-paso-inicial="{{ $pasoInicial }}" data-url-obra="{{ route('recepcion.obra') }}">
                @csrf

                {{-- ================= PASO 1 ================= --}}
                <div class="paso-form" data-paso="1">
                    <div class="row g-3">

                        <div class="col-12">
                            <label class="form-label">Folio</label>
                            <input type="text" class="form-control" value="{{ $folioGenerado }}" readonly>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Clave de obra</label>
                            <input type="text" name="clave_obra" id="clave_obra" class="form-control" placeholder="Ej. OB-001" value="{{ old('clave_obra') }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Cliente</label>
                            <input type="text" name="cliente" id="cliente" class="form-control" placeholder="Ej. Obra XYZ S.A. de C.V." value="{{ old('cliente') }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Obra</label>
                            <input type="text" name="obra" id="obra" class="form-control" placeholder="Ej. Edificio ABC" value="{{ old('obra') }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Atención a:</label>
                            <input type="text" name="atencion_a" id="atencion_a" class="form-control" placeholder="Ej. Ing. Juan Pérez" value="{{ old('atencion_a') }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Número de informe</label>
                            <input type="text" name="numero_informe" class="form-control" placeholder="Ej. INF-001" value="{{ old('numero_informe') }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">No. Especímenes</label>
                            <select name="num_especimenes" id="num_especimenes" class="form-select" required>
                                <option value="" selected disabled>--</option>
                                @for($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}" {{ old('num_especimenes') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                            </select>
                        </div>

                    </div>
                </div>

                {{-- ================= PASO 2: DATOS POR ESPÉCIMEN ================= --}}
                <div class="paso-form d-none" data-paso="2">
                    <div class="row g-3">

                        @for($i = 0; $i < 5; $i++)
                            <div class="col-12 especimen-bloque d-none" data-indice="{{ $i }}">
                            <div class="especimen-card">
                                <h6 class="especimen-titulo">Espécimen {{ $i + 1 }}</h6>
                                <div class="row g-3">

                                    <div class="col-12">
                                        <label class="form-label">Número de muestra</label>
                                        <input type="text" name="especimenes[{{ $i }}][numero_muestra]" class="form-control"
                                            placeholder="Ej. MUE-001" required
                                            value="{{ old('especimenes.'.$i.'.numero_muestra') }}">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Elemento</label>
                                        <select class="form-select elemento-select" required>
                                            <option value="" selected disabled>Seleccione...</option>
                                            @foreach($elementos as $el)
                                            <option value="{{ $el->nombre }}">{{ $el->nombre }}</option>
                                            @endforeach
                                            <option value="__otro__">Otro (especificar)</option>
                                        </select>
                                        <input type="text" class="form-control mt-2 d-none elemento-otro"
                                            placeholder="Escribe el elemento" autocomplete="off">
                                        <input type="hidden" class="elemento-hidden" name="especimenes[{{ $i }}][elemento]"
                                            value="{{ old('especimenes.'.$i.'.elemento') }}">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Espesor (mm)</label>
                                        <input type="number" name="especimenes[{{ $i }}][espesor]" class="form-control"
                                            step="0.01" min="0" inputmode="decimal" required
                                            value="{{ old('especimenes.'.$i.'.espesor') }}">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Localización</label>
                                        <input type="text" name="especimenes[{{ $i }}][localizacion]" class="form-control"
                                            placeholder="Ej. Eje 3, Nivel 2" required
                                            value="{{ old('especimenes.'.$i.'.localizacion') }}">
                                    </div>

                                </div>
                            </div>
                    </div>
                    @endfor

                </div>
        </div>

        {{-- ================= PASO 3 ================= --}}
        <div class="paso-form d-none" data-paso="3">
            <div class="row g-3">

                <div class="col-12">
                    <label class="form-label">Fecha de colado</label>
                    <input type="date" name="fecha_colado" id="fecha_colado" class="form-control"
                        value="{{ old('fecha_colado', date('Y-m-d')) }}">
                </div>

                <div class="col-12">
                    <label class="form-label">Fecha de muestreo</label>
                    <input type="date" name="fecha_muestreo" id="fecha_muestreo" class="form-control"
                        value="{{ old('fecha_muestreo', date('Y-m-d')) }}">
                </div>

                <div class="col-12">
                    <label class="form-label">Fecha de recepción</label>
                    <input type="date" name="fecha_recepcion" id="fecha_recepcion" class="form-control"
                        value="{{ old('fecha_recepcion', date('Y-m-d')) }}">
                </div>

                <div class="col-12">
                    <label class="form-label">Datos de proyecto</label>
                    <textarea name="datos_proyecto" class="form-control" rows="3">{{ old('datos_proyecto') }}</textarea>
                </div>

                <div class="col-12">
                    <label class="form-label">Defectos del espécimen</label>
                    <textarea name="defectos_especimen" class="form-control" rows="3">{{ old('defectos_especimen') }}</textarea>
                </div>

            </div>
        </div>

        {{-- ================= PASO 4 ================= --}}
        <div class="paso-form d-none" data-paso="4">
            <div class="row g-3">

                <div class="col-12">
                    <label class="form-label d-block">Fechas de ensayo</label>
                    <small class="text-muted d-block mb-2" id="limite-fechas-msg">Selecciona el número de especímenes primero.</small>
                    <div class="d-flex flex-column gap-2">
                        @foreach([1, 3, 7, 14, 28] as $dia)
                        <div class="ensayo-fila d-flex align-items-center justify-content-between">
                            <div class="form-check d-flex align-items-center gap-2 mb-0">
                                <input class="form-check-input" type="checkbox" name="fechas_ensayo[]"
                                    value="{{ $dia }}" id="ensayo{{ $dia }}" data-dias="{{ $dia }}"
                                    {{ in_array($dia, old('fechas_ensayo', [])) ? 'checked' : '' }}>
                                <label class="form-check-label mb-0" for="ensayo{{ $dia }}">
                                    {{ $dia }}
                                </label>
                            </div>
                            <span class="fecha-aproximada-box" id="fecha-ensayo-{{ $dia }}">--</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label">Envía</label>
                    <input type="text" name="envia" class="form-control" placeholder="Nombre de quien envía" value="{{ old('envia') }}">
                </div>

                <div class="col-12">
                    <label class="form-label">Recibe</label>
                    <input type="text" name="recibe" class="form-control" placeholder="Nombre de quien recibe"
                        value="{{ old('recibe', $nombreCompletoUsuario) }}">
                </div>

                <div class="col-12">
                    <label class="form-label">Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="2">{{ old('observaciones') }}</textarea>
                </div>

            </div>
        </div>

        <div class="text-center mt-4 d-none" id="contenedorGuardar">
            <button type="submit" class="btn btn-guardar">Guardar Recepción</button>
        </div>
        </form>

        <nav class="mt-2" aria-label="Pasos del registro">
            <ul class="pagination justify-content-center" id="paginacionPasos">
                <li class="page-item" id="pagAnterior">
                    <a class="page-link" role="button">Anterior</a>
                </li>
                <li class="page-item" data-ir-paso="1">
                    <a class="page-link" role="button">1</a>
                </li>
                <li class="page-item" data-ir-paso="2">
                    <a class="page-link" role="button">2</a>
                </li>
                <li class="page-item" data-ir-paso="3">
                    <a class="page-link" role="button">3</a>
                </li>
                <li class="page-item" data-ir-paso="4">
                    <a class="page-link" role="button">4</a>
                </li>
                <li class="page-item" id="pagSiguiente">
                    <a class="page-link" role="button">Siguiente</a>
                </li>
            </ul>
        </nav>

    </div>
</div>
</div>

<script src="{{ asset('js/pages/Recepcion.js') }}"></script>

@endsection