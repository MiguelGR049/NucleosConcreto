@extends('plantilla')
@section('contenido')

@section('navbar-title', 'Captura de propiedades')
@section('back-route', route('inicio'))

@include('layouts.navbar')

@php
// k = clave | l = etiqueta | t = texto, num, info (solo lectura) o calc (se calcula) | d = decimales
$filas = [
['k' => 'no_nucleo', 'l' => 'No. de muestra / núcleo', 't' => 'select'],
['k' => 'elemento', 'l' => 'Elemento', 't' => 'info'],
['k' => 'espesor', 'l' => 'Espesor mm', 't' => 'info'],
['k' => 'localizacion', 'l' => 'Localización', 't' => 'info'],
['k' => 'dia', 'l' => 'Edad de ensayo días', 't' => 'info'],
['k' => 'masa_g', 'l' => 'Masa del espécimen g', 't' => 'num'],
['k' => 'masa_unitaria', 'l' => 'Masa unitaria kg/m³', 't' => 'calc', 'd' => 2],
['k' => 'diametro_1_mm', 'l' => 'Diámetro 1 mm', 't' => 'num'],
['k' => 'diametro_2_mm', 'l' => 'Diámetro 2 mm', 't' => 'num'],
['k' => 'diametro_prom', 'l' => 'Diámetro promedio mm', 't' => 'calc', 'd' => 2],
['k' => 'altura_1_sin_cabeceo_mm', 'l' => 'Altura 1 sin cabeceo mm', 't' => 'num'],
['k' => 'altura_2_sin_cabeceo_mm', 'l' => 'Altura 2 sin cabeceo mm', 't' => 'num'],
['k' => 'altura_prom_sin', 'l' => 'Altura promedio sin cabeceo mm','t' => 'calc', 'd' => 2],
['k' => 'altura_1_con_cabeceo_mm', 'l' => 'Altura 1 con cabeceo mm', 't' => 'num'],
['k' => 'altura_2_con_cabeceo_mm', 'l' => 'Altura 2 con cabeceo mm', 't' => 'num'],
['k' => 'altura_prom_con', 'l' => 'Altura promedio con cabeceo mm','t' => 'calc', 'd' => 2],
['k' => 'esbeltez', 'l' => 'Esbeltez (h/d)', 't' => 'calc', 'd' => 2],
['k' => 'factor', 'l' => 'Factor de corrección', 't' => 'calc', 'd' => 2],
['k' => 'area', 'l' => 'Área cm²', 't' => 'calc', 'd' => 2],
];
@endphp

<div class="container-fluid px-3 px-md-4 mt-4 mb-4">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow propiedades-card">
                <div class="card-body p-3 p-md-4">

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
                    <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div id="aviso-captura" class="alert alert-warning d-none" role="alert"></div>

                    <h5 class="mb-3">Fechas de ensayo</h5>

                    <div class="d-flex align-items-end gap-2 gap-md-3 mb-4">
                        <form method="GET" action="{{ route('propiedades') }}" class="flex-grow-1">
                            <input type="date" id="fecha" name="fecha"
                                class="form-control form-control-lg"
                                value="{{ $fecha }}" onchange="this.form.submit()">
                        </form>

                        <button type="button" id="btn-captura"
                            class="btn btn-lg btn-captura px-3 px-md-4 flex-shrink-0 text-nowrap">
                            <i class="bi bi-clipboard-check"></i>
                            <span class="d-none d-sm-inline ms-1">Captura</span>
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle text-center mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Folio</th>
                                    <th>Clave</th>
                                    <th>Muestreo</th>
                                    <th>Observaciones</th>
                                    <th>Ensayar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($muestras as $m)
                                <tr class="{{ $m->capturada ? 'table-secondary text-muted' : '' }}">
                                    <td class="fw-semibold">{{ $m->folio }}</td>
                                    <td class="fw-semibold">{{ $m->clave_obra }}</td>
                                    <td>{{ \Carbon\Carbon::parse($m->muestreo)->format('d/m/Y') }}</td>
                                    <td class="text-start">{{ $m->observaciones ?: '—' }}</td>
                                    <td>
                                        <input type="checkbox" class="form-check-input chk-ensayar"
                                            data-id="{{ $m->id_seg }}"
                                            data-clave-obra="{{ $m->clave_obra }}"
                                            data-obra="{{ $m->obra }}"
                                            data-extraccion="{{ \Carbon\Carbon::parse($m->muestreo)->format('d/m/Y') }}"
                                            data-datos="{{ $m->datos_proy }}"
                                            data-nucleos="{{ json_encode($m->nucleos) }}"
                                            data-dia="{{ $m->dia }}"
                                            data-max="{{ $m->max_nucleos }}"
                                            data-restantes="{{ $m->restantes }}"
                                            data-pendientes="{{ $m->pendientes }}"
                                            {{ $m->capturada ? 'checked disabled' : '' }}>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-muted py-4">No hay muestras para esta fecha</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- ================= MODAL: HOJA DE RESULTADOS ================= --}}
<div class="modal fade hoja-modal" id="modalHoja" tabindex="-1" aria-labelledby="modalHojaTitulo" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-lg-down">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalHojaTitulo">Captura de propiedades</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="formHoja" method="POST" action="{{ route('propiedades.guardar') }}">
                @csrf
                <input type="hidden" name="fecha" value="{{ $fecha }}">
                <input type="hidden" name="fecha_ensayo_id" id="h-fecha-ensayo-id">

                <div class="modal-body">

                    {{-- Encabezado de la hoja --}}
                    <div class="row g-2 g-md-3 mb-3">
                        <div class="col-6 col-md-3">
                            <label class="form-label">Fecha de ensayo</label>
                            <input type="text" class="form-control bg-light" readonly
                                value="{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Clave de obra</label>
                            <input type="text" id="h-clave-obra" class="form-control bg-light" readonly>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Obra</label>
                            <input type="text" id="h-obra" class="form-control bg-light" readonly>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Fecha de extracción</label>
                            <input type="text" id="h-extraccion" class="form-control bg-light" readonly>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Datos de proyecto</label>
                            <textarea id="h-datos" class="form-control bg-light" rows="2" readonly></textarea>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="tmn">T.M.N.</label>
                            <input type="text" inputmode="decimal" autocomplete="off"
                                id="tmn" class="form-control campo-num">
                            <input type="hidden" name="tmn">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="resistencia">Resistencia kg/cm²</label>
                            <input type="text" inputmode="decimal" autocomplete="off"
                                id="resistencia" class="form-control campo-num">
                            <input type="hidden" name="resistencia_proyecto">
                        </div>
                    </div>

                    {{-- Cuántos núcleos se capturan --}}
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <div class="flex-grow-1">
                            <div class="fw-semibold" id="contador-nucleos">Núcleos: 1</div>
                            <small class="text-muted" id="info-capacidad"></small>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-nucleo" id="btn-quitar-nucleo">
                                <i class="bi bi-dash-lg"></i> Quitar
                            </button>
                            <button type="button" class="btn btn-nucleo" id="btn-agregar-nucleo">
                                <i class="bi bi-plus-lg"></i> Agregar núcleo
                            </button>
                        </div>
                    </div>

                    {{-- Tabla de resultados --}}
                    <div class="hoja-titulo">TABLA DE RESULTADOS</div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle mb-0 hoja-tabla" id="tabla-hoja">
                            <tbody>
                                @foreach ($filas as $f)
                                <tr>
                                    <th scope="row" class="hoja-label">{{ $f['l'] }}</th>
                                    @for ($c = 0; $c < 4; $c++)
                                        <td class="hoja-celda {{ $f['t'] === 'calc' ? 'celda-calc' : '' }} d-none"
                                        data-col="{{ $c }}"
                                        data-campo="{{ $f['k'] }}">
                                        @if ($f['t'] === 'info')
                                        <span data-info="{{ $f['k'] }}">—</span>
                                        @elseif ($f['t'] === 'calc')
                                        <span data-calc="{{ $f['k'] }}" data-dec="{{ $f['d'] }}">—</span>
                                        @elseif ($f['t'] === 'select')
                                        <select class="form-select form-select-sm select-nucleo"
                                            name="nucleos[{{ $c }}][id_nucleo]" disabled>
                                            <option value="">Seleccione...</option>
                                        </select>
                                        @else
                                        {{-- Visible: muestra 2 decimales. Oculto: guarda el valor real y es el que se envía --}}
                                        <input type="text" inputmode="decimal" autocomplete="off"
                                            class="form-control form-control-sm campo-num" disabled>
                                        <input type="hidden" name="nucleos[{{ $c }}][{{ $f['k'] }}]" disabled>
                                        @endif
                                        </td>
                                        @endfor
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <small class="text-muted d-block mt-2">
                        Los renglones con fondo punteado se calculan solos y no se guardan.
                        Los números se muestran con 2 decimales, pero los cálculos usan el valor completo que escribiste (hasta 4 decimales).
                    </small>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-guardar" id="btn-guardar-hoja">Guardar</button>
                </div>
            </form>

        </div>
    </div>
</div>

<script src="{{ asset('js/pages/Propiedades.js') }}"></script>

@endsection