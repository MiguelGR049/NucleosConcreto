@extends('plantilla')
@section('contenido')

@section('navbar-title', 'Corrección de datos')
@section('back-route', route('inicio'))

@include('layouts.navbar')

@php
// t = info (solo lectura) | txt (texto editable) | num (número editable) | calc (se calcula) | sel (tipo de falla)
// req = obligatorio | up = forzar mayúsculas
$filas = [
['k' => 'no_nucleo', 'l' => 'No. de muestra / núcleo', 't' => 'info'],
['k' => 'elemento', 'l' => 'Elemento', 't' => 'info'],
['k' => 'espesor', 'l' => 'Espesor mm', 't' => 'num'],
['k' => 'localizacion', 'l' => 'Localización', 't' => 'info'],
['k' => 'dia', 'l' => 'Edad de ensayo días', 't' => 'info'],
['k' => 'masa_g', 'l' => 'Masa del espécimen g', 't' => 'num', 'req' => true],
['k' => 'masa_unitaria', 'l' => 'Masa unitaria kg/m³', 't' => 'calc'],
['k' => 'diametro_1_mm', 'l' => 'Diámetro 1 mm', 't' => 'num', 'req' => true],
['k' => 'diametro_2_mm', 'l' => 'Diámetro 2 mm', 't' => 'num', 'req' => true],
['k' => 'diametro_prom', 'l' => 'Diámetro promedio mm', 't' => 'calc'],
['k' => 'altura_1_sin_cabeceo_mm', 'l' => 'Altura 1 sin cabeceo mm', 't' => 'num', 'req' => true],
['k' => 'altura_2_sin_cabeceo_mm', 'l' => 'Altura 2 sin cabeceo mm', 't' => 'num', 'req' => true],
['k' => 'altura_prom_sin', 'l' => 'Altura promedio sin cabeceo mm', 't' => 'calc'],
['k' => 'altura_1_con_cabeceo_mm', 'l' => 'Altura 1 con cabeceo mm', 't' => 'num'],
['k' => 'altura_2_con_cabeceo_mm', 'l' => 'Altura 2 con cabeceo mm', 't' => 'num'],
['k' => 'altura_prom_con', 'l' => 'Altura promedio con cabeceo mm', 't' => 'calc'],
['k' => 'esbeltez', 'l' => 'Esbeltez (h/d)', 't' => 'calc'],
['k' => 'factor', 'l' => 'Factor de corrección', 't' => 'calc'],
['k' => 'area', 'l' => 'Área cm²', 't' => 'calc'],
['k' => 'carga_kn', 'l' => 'Carga aplicada kN', 't' => 'num'],
['k' => 'carga_kg', 'l' => 'Carga kgf', 't' => 'calc'],
['k' => 'esf_directo', 'l' => 'Esfuerzo directo kg/cm²', 't' => 'calc'],
['k' => 'esf_real', 'l' => 'Esfuerzo real kg/cm²', 't' => 'calc'],
['k' => 'esf_real_mpa', 'l' => 'Esfuerzo real MPa', 't' => 'calc'],
['k' => 'tipo_falla', 'l' => 'Tipo de falla', 't' => 'sel'],
];
@endphp

<div class="container mt-4 mb-5">

    @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
    <div class="alert alert-danger">
        <strong>Revisa los siguientes datos:</strong>
        <ul class="mb-0">
            @foreach ($errors->unique() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="card recepcion-card mb-3">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('correccion') }}">
                <label for="q" class="form-label">Clave de obra o folio</label>
                <div class="input-group">
                    <input type="text" id="q" name="q" class="form-control text-uppercase" value="{{ $q }}" placeholder="Ej. 1A26OCT o AB-00" autofocus>
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if ($hojas->isEmpty())
    <div class="alert alert-warning">
        {{ $q !== '' ? "No se encontraron hojas de ensayo para \"{$q}\"." : 'Aún no hay hojas de ensayo capturadas.' }}
    </div>
    @endif

    @foreach ($hojas as $h)
    <div class="card recepcion-card mb-3">
        <div class="card-body p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <strong>{{ $h->folio }}</strong> · {{ $h->clave_obra }}<br>
                <small class="text-muted">
                    {{ $h->obra ?: '-' }} · Ensayo a {{ $h->dia }} días ·
                    {{ \Carbon\Carbon::parse($h->fecha_ensayo)->format('d/m/Y') }}
                </small><br>
                <span class="badge {{ $h->ensayada ? 'bg-success' : 'bg-warning text-dark' }}">
                    {{ $h->ensayada ? 'Ensayada' : 'Pendiente' }}
                </span>
                <span class="badge bg-secondary">{{ $h->nucleos->count() }} núcleo(s)</span>
            </div>
            <button type="button" class="btn btn-primary btn-editar"
                data-bs-toggle="modal" data-bs-target="#modalEdicion"
                data-hoja="{{ json_encode($h) }}">
                <i class="bi bi-pencil-square"></i> Editar
            </button>
        </div>
    </div>
    @endforeach
</div>

{{-- ================= MODAL: EDICIÓN DE LA HOJA ================= --}}
<div class="modal fade hoja-modal" id="modalEdicion" tabindex="-1" aria-labelledby="modalEdicionTitulo" aria-hidden="true"
    data-old="{{ json_encode(old()) }}"
    data-hay-errores="{{ $errors->any() ? '1' : '0' }}">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-lg-down">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalEdicionTitulo">
                    Corrección · Folio <span id="e-folio-titulo"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="formEdicion" method="POST" action="{{ route('correccion.guardar') }}">
                @csrf
                <input type="hidden" name="hoja_id" id="e-hoja-id">
                <input type="hidden" name="q" value="{{ $q }}">

                <div class="modal-body">

                    {{-- Encabezado (solo lectura) --}}
                    <div class="row g-2 g-md-3 mb-3">
                        <div class="col-6 col-md-3">
                            <label class="form-label">Fecha de ensayo</label>
                            <input type="text" id="e-fecha" class="form-control bg-light" readonly>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Clave de obra</label>
                            <input type="text" id="e-clave-obra" class="form-control bg-light" readonly>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Obra</label>
                            <input type="text" id="e-obra" class="form-control bg-light" readonly>
                        </div>
                    </div>

                    <div class="hoja-titulo">TABLA DE RESULTADOS</div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle mb-0 hoja-tabla" id="tabla-edicion">
                            <tbody>
                                @foreach ($filas as $f)
                                <tr>
                                    <th scope="row" class="hoja-label">{{ $f['l'] }}</th>
                                    @for ($c = 0; $c < 4; $c++)
                                        <td class="hoja-celda {{ in_array($f['t'], ['calc', 'info']) ? 'celda-calc' : '' }} d-none"
                                        data-col="{{ $c }}"
                                        data-campo="{{ $f['k'] }}"
                                        data-req="{{ !empty($f['req']) ? '1' : '0' }}">
                                        @if ($f['t'] === 'info')
                                        <span data-info="{{ $f['k'] }}">—</span>
                                        @elseif ($f['t'] === 'calc')
                                        <span data-calc="{{ $f['k'] }}">—</span>
                                        @elseif ($f['t'] === 'sel')
                                        <select class="form-select form-select-sm text-center campo-sel">
                                            <option value="">—</option>
                                            @foreach (['I', 'II', 'III', 'IV', 'V', 'VI'] as $tf)
                                            <option value="{{ $tf }}">{{ $tf }}</option>
                                            @endforeach
                                        </select>
                                        @elseif ($f['t'] === 'txt')
                                        <input type="text" autocomplete="off"
                                            class="form-control form-control-sm campo-txt {{ !empty($f['up']) ? 'text-uppercase' : '' }}"
                                            {{ !empty($f['req']) ? 'required' : '' }}>
                                        @else
                                        {{-- Visible: 2 decimales. Oculto: valor real, es el que se envía --}}
                                        <input type="text" inputmode="decimal" autocomplete="off"
                                            class="form-control form-control-sm campo-num">
                                        <input type="hidden">
                                        @endif
                                        </td>
                                        @endfor
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger me-auto" id="btn-eliminar-edicion"
                        formaction="{{ route('correccion.eliminar') }}" formnovalidate>
                        <i class="bi bi-trash"></i> Eliminar registro
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-guardar" id="btn-guardar-edicion">Guardar cambios</button>
                </div>
            </form>

        </div>
    </div>
</div>

<script src="{{ asset('js/pages/Edicion.js') }}"></script>

@endsection