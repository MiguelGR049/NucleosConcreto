@extends('plantilla')
@section('contenido')

@section('navbar-title', 'Consulta resultado')
@section('back-route', route('inicio'))

@include('layouts.navbar')

<div class="container mt-4 mb-5">
    <div class="card recepcion-card mb-3">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('consulta') }}">
                <label for="q" class="form-label">Clave de obra o folio</label>
                <div class="input-group">
                    <input type="text" id="q" name="q" class="form-control text-uppercase" value="{{ $q }}" placeholder="Ej. 1A26OCT o AB-00" autofocus>
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                </div>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="todas" id="todas" value="1"
                        {{ $todas ? 'checked' : '' }} onchange="this.form.submit()">
                    <label class="form-check-label" for="todas">Incluir recepciones sin ensayos</label>
                </div>
            </form>
        </div>
    </div>

    @if($resultados->isEmpty())
    <div class="alert alert-warning">
        {{ $q !== '' ? "No se encontraron pruebas con ensayos para \"{$q}\"." : 'Aún no hay pruebas ensayadas.' }}
    </div>
    @endif

    @foreach($resultados as $r)
    <div class="card recepcion-card mb-3">
        <div class="card-body p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <strong>{{ $r->folio }}</strong> · {{ $r->clave_obra }}<br>
                <small class="text-muted">
                    {{ $r->elementos ?: '-' }} · Recibida {{ \Carbon\Carbon::parse($r->fecha_rec)->format('d/m/Y') }}
                </small><br>
                <span class="badge {{ $r->ensayadas === $r->total ? 'bg-success' : 'bg-warning text-dark' }}">
                    {{ $r->ensayadas }} de {{ $r->total }} ensayadas
                </span>
            </div>
            <button type="button" class="btn btn-primary btn-generar"
                data-bs-toggle="modal" data-bs-target="#modalDocumento"
                data-resultado="{{ json_encode($r) }}">
                <i class="bi bi-file-earmark-text"></i> Generar documento
            </button>
        </div>
    </div>
    @endforeach
</div>

{{-- ================= MODAL: GENERAR DOCUMENTO ================= --}}
<div class="modal fade" id="modalDocumento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-fullscreen-lg-down modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generar documento <span id="mdFolio"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="formDocumento" method="POST" action="{{ route('consulta.pdf') }}" class="d-contents">
                @csrf
                <input type="hidden" name="payload" id="mdPayload">
                <input type="hidden" name="recepcion_id" id="mdRecepcionId">

                <div class="modal-body">

                    {{-- Encabezado --}}
                    <h6 class="seccion-titulo">Encabezado</h6>
                    <div class="row g-2 mb-3">
                        @foreach([
                        ['mdInforme', 'Informe'],
                        ['mdNInforme', 'Número de informe'],
                        ['mdClave', 'Clave de obra'],
                        ['mdCliente', 'Cliente'],
                        ['mdObra', 'Obra'],
                        ['mdAtencion', 'Con atención a'],
                        ['mdElemento', 'Elemento'],
                        ['mdRecepcion', 'Fecha de recepción'],
                        ['mdExtraccion', 'Fecha de extracción'],
                        ] as [$id, $label])
                        <div class="col-12 col-md-6">
                            <label class="form-label small mb-1">{{ $label }}</label>
                            <input type="text" id="{{ $id }}" class="form-control form-control-sm" readonly>
                        </div>
                        @endforeach

                        <div class="col-12 col-md-6">
                            <label class="form-label small mb-1" for="mdFechaInforme">Fecha de informe</label>
                            <input type="date" name="fecha_informe" id="mdFechaInforme"
                                class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small mb-1" for="mdProcedencia">Procedencia</label>
                            <input type="text" name="procedencia" id="mdProcedencia" class="form-control form-control-sm" readonly>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small mb-1" for="mdMuestreadoPor">Muestreado por</label>
                            <select name="muestreado_por" id="mdMuestreadoPor" class="form-select form-select-sm">
                                <option value="---">---</option>
                                <option value="LIEC">LIEC</option>
                                <option value="CLIENTE">CLIENTE</option>
                            </select>
                        </div>
                    </div>

                    {{-- Datos de proyecto --}}
                    <h6 class="seccion-titulo">Datos de proyecto</h6>
                    <div class="row g-2 mb-3">
                        <div class="col-6 col-md-4">
                            <label class="form-label small mb-1" for="mdTn">T. N., mm</label>
                            <input type="number" step="any" name="tn" id="mdTn" class="form-control form-control-sm">
                        </div>
                        <div class="col-6 col-md-4">
                            <label class="form-label small mb-1" for="mdRevenimiento">Revenimiento, cm</label>
                            <input type="number" step="any" name="revenimiento" id="mdRevenimiento" class="form-control form-control-sm">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small mb-1" for="mdResistencia">Resistencia de proyecto, kg/cm²</label>
                            <input type="number" step="any" name="resistencia_proyecto" id="mdResistencia" class="form-control form-control-sm">
                        </div>
                        <div class="col-6">
                            <label class="form-label small mb-1" for="mdConcreto">Concreto</label>
                            <select name="tipo_concreto" id="mdConcreto" class="form-select form-select-sm">
                                @foreach(['---', 'NORMAL', 'RAPIDO'] as $op)
                                <option value="{{ $op }}">{{ $op }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small mb-1" for="mdCemento">Tipo de cemento</label>
                            <select name="tipo_cemento" id="mdCemento" class="form-select form-select-sm">
                                @foreach(['---', 'CPC', 'CPO', 'CPP'] as $op)
                                <option value="{{ $op }}">{{ $op }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small mb-1">Datos de proyecto (recepción)</label>
                            <textarea id="mdDatosProy" class="form-control form-control-sm" rows="2" readonly></textarea>
                        </div>
                    </div>

                    {{-- Tabla de resultados --}}
                    <h6 class="seccion-titulo">Tabla de resultados</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-bordered tabla-resultados mb-0">
                            <tbody id="mdTabla"></tbody>
                        </table>
                    </div>

                    {{-- Pie --}}
                    <div class="row g-2">
                        <div class="col-6 col-md-3">
                            <label class="form-label small mb-1">Recibe</label>
                            <input type="text" id="mdRecibe" class="form-control form-control-sm" readonly>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small mb-1">Defectos del espécimen</label>
                            <input type="text" id="mdDefectos" class="form-control form-control-sm" readonly>
                        </div>
                        <div class="col-12">
                            <label class="form-label small mb-1" for="mdConformidad">Declaración de conformidad</label>
                            <textarea name="declaracion_conformidad" id="mdConformidad"
                                class="form-control form-control-sm fw-semibold" rows="2" readonly></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label small mb-1" for="mdObservHoja">Observaciones</label>
                            <textarea name="observaciones_hoja" id="mdObservHoja"
                                class="form-control form-control-sm" rows="2" readonly></textarea>
                        </div>
                    </div>

                    <div class="alert alert-warning mt-3 mb-0 d-none" id="mdAviso">
                        Hay ensayos pendientes; el documento saldrá incompleto.
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="variante" value="sin" formtarget="_blank" class="btn btn-outline-primary">
                        <i class="bi bi-file-earmark-pdf"></i> PDF sin imagen
                    </button>
                    <button type="submit" name="variante" value="con" formtarget="_blank" class="btn btn-primary">
                        <i class="bi bi-file-earmark-pdf"></i> PDF con imagen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="{{ asset('js/pages/Consulta.js') }}"></script>

@endsection