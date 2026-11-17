@extends('plantilla')

@section('navbar-title', 'Ensayo de núcleos')
@section('back-route', route('inicio'))

@section('contenido')
@include('layouts.navbar')

<div class="container ensayo-contenedor mt-4 mb-5">

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('info'))
    <div class="alert alert-info">{{ session('info') }}</div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger">
        <strong>Revisa los siguientes datos:</strong>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @forelse($pendientes as $nucleo)

    <div class="card ensayo-card mb-3">
        <div class="ensayo-card-body">

            <div class="d-flex flex-wrap justify-content-between align-items-start mb-3">
                <div>
                    <div class="ensayo-folio mb-2">Folio: {{ $nucleo->folio }}</div>
                    <span class="ensayo-nucleo-badge">Núcleo {{ $nucleo->no_nucleo }}</span>
                </div>
                <span class="badge bg-warning text-dark">Pendiente</span>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha de ensayo</th>
                            <th>Clave de obra</th>
                            <th>Obra</th>
                            <th>Elemento</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="fw-semibold">
                            <td>{{ \Carbon\Carbon::parse($nucleo->fecha_ensayo)->format('d/m/Y') }}</td>
                            <td class="fw-semibold">{{ $nucleo->clave_obra }}</td>
                            <td class="fw-semibold">{{ $nucleo->obra }}</td>
                            <td class="text-start">{{ $nucleo->elemento }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="text-end mt-2">
                <button
                    type="button"
                    class="btn btn-captura btn-ensayar"
                    data-bs-toggle="modal"
                    data-bs-target="#modalEnsayo"
                    data-id="{{ $nucleo->id_seg }}"
                    data-folio="{{ $nucleo->folio }}"
                    data-nucleo="{{ $nucleo->no_nucleo }}"
                    data-fecha="{{ \Carbon\Carbon::parse($nucleo->fecha_ensayo)->format('d/m/Y') }}"
                    data-clave-obra="{{ $nucleo->clave_obra }}"
                    data-obra="{{ $nucleo->obra }}"
                    data-elemento="{{ $nucleo->elemento }}"
                    data-espesor="{{ $nucleo->espesor }}"
                    data-localizacion="{{ $nucleo->localizacion }}"
                    data-dia="{{ $nucleo->dia }}"
                    data-masa_g="{{ $nucleo->masa_g }}"
                    data-diametro_1_mm="{{ $nucleo->diametro_1_mm }}"
                    data-diametro_2_mm="{{ $nucleo->diametro_2_mm }}"
                    data-altura_1_sin_cabeceo_mm="{{ $nucleo->altura_1_sin_cabeceo_mm }}"
                    data-altura_2_sin_cabeceo_mm="{{ $nucleo->altura_2_sin_cabeceo_mm }}"
                    data-altura_1_con_cabeceo_mm="{{ $nucleo->altura_1_con_cabeceo_mm }}"
                    data-altura_2_con_cabeceo_mm="{{ $nucleo->altura_2_con_cabeceo_mm }}"
                    data-carga_kn="{{ $nucleo->carga_kn }}"
                    data-tipo_falla="{{ $nucleo->tipo_falla }}">
                    Ensayar
                </button>
            </div>

        </div>
    </div>

    @empty

    <div class="card ensayo-card">
        <div class="card-body text-center py-5">
            <h5 class="text-success">No hay núcleos pendientes</h5>
            <p class="text-muted mb-0">
                Todos los núcleos disponibles ya tienen su ensayo completo.
            </p>
        </div>
    </div>

    @endforelse

</div>

{{-- Modal único: se llena con los datos del núcleo seleccionado --}}
@php
// t = info | num (editable) | calc | txt
// dep = datos que, si faltan, mandan el renglón a "Por capturar"
// (si no se indica, depende de sí mismo; info no depende de nada)
$filas = [
['k' => 'elemento', 'l' => 'Elemento', 't' => 'info', 'dep' => []],
['k' => 'espesor', 'l' => 'Espesor mm', 't' => 'info', 'dep' => []],
['k' => 'localizacion', 'l' => 'Localización', 't' => 'info', 'dep' => []],
['k' => 'dia', 'l' => 'Edad de ensayo días', 't' => 'info', 'dep' => []],
['k' => 'masa_g', 'l' => 'Masa del espécimen g', 't' => 'num'],
['k' => 'masa_unitaria', 'l' => 'Masa unitaria kg/m³', 't' => 'calc',
'dep' => ['masa_g', 'diametro_1_mm', 'diametro_2_mm', 'altura_1_sin_cabeceo_mm', 'altura_2_sin_cabeceo_mm']],
['k' => 'diametro_1_mm', 'l' => 'Diámetro 1 mm', 't' => 'num'],
['k' => 'diametro_2_mm', 'l' => 'Diámetro 2 mm', 't' => 'num'],
['k' => 'diametro_prom', 'l' => 'Diámetro promedio mm', 't' => 'calc',
'dep' => ['diametro_1_mm', 'diametro_2_mm']],
['k' => 'altura_1_sin_cabeceo_mm', 'l' => 'Altura 1 sin cabeceo mm', 't' => 'num'],
['k' => 'altura_2_sin_cabeceo_mm', 'l' => 'Altura 2 sin cabeceo mm', 't' => 'num'],
['k' => 'altura_prom_sin', 'l' => 'Altura promedio sin cabeceo mm', 't' => 'calc',
'dep' => ['altura_1_sin_cabeceo_mm', 'altura_2_sin_cabeceo_mm']],
['k' => 'altura_1_con_cabeceo_mm', 'l' => 'Altura 1 con cabeceo mm', 't' => 'num'],
['k' => 'altura_2_con_cabeceo_mm', 'l' => 'Altura 2 con cabeceo mm', 't' => 'num'],
['k' => 'altura_prom_con', 'l' => 'Altura promedio con cabeceo mm', 't' => 'calc',
'dep' => ['altura_1_con_cabeceo_mm', 'altura_2_con_cabeceo_mm']],
['k' => 'esbeltez', 'l' => 'Esbeltez (h/d)', 't' => 'calc',
'dep' => ['altura_1_con_cabeceo_mm', 'altura_2_con_cabeceo_mm', 'diametro_1_mm', 'diametro_2_mm']],
['k' => 'factor', 'l' => 'Factor de corrección', 't' => 'calc',
'dep' => ['altura_1_con_cabeceo_mm', 'altura_2_con_cabeceo_mm', 'diametro_1_mm', 'diametro_2_mm']],
['k' => 'area', 'l' => 'Área cm²', 't' => 'calc',
'dep' => ['diametro_1_mm', 'diametro_2_mm']],
['k' => 'carga_kn', 'l' => 'Carga aplicada kN', 't' => 'num'],
['k' => 'carga_kg', 'l' => 'Carga kgf', 't' => 'calc',
'dep' => ['carga_kn']],
['k' => 'esf_directo', 'l' => 'Esfuerzo directo kg/cm²', 't' => 'calc',
'dep' => ['carga_kn', 'diametro_1_mm', 'diametro_2_mm']],
['k' => 'esf_real', 'l' => 'Esfuerzo real kg/cm²', 't' => 'calc',
'dep' => ['carga_kn', 'diametro_1_mm', 'diametro_2_mm', 'altura_1_con_cabeceo_mm', 'altura_2_con_cabeceo_mm']],
['k' => 'esf_real_mpa', 'l' => 'Esfuerzo real MPa', 't' => 'calc',
'dep' => ['carga_kn', 'diametro_1_mm', 'diametro_2_mm', 'altura_1_con_cabeceo_mm', 'altura_2_con_cabeceo_mm']],
['k' => 'tipo_falla', 'l' => 'Tipo de falla', 't' => 'sel'],
];
@endphp

{{-- Modal único: se llena con los datos del núcleo seleccionado --}}
<div class="modal fade hoja-modal ensayo-modal" id="modalEnsayo" tabindex="-1" aria-labelledby="modalEnsayoTitulo" aria-hidden="true"
    data-old="{{ json_encode(old()) }}"
    data-hay-errores="{{ $errors->any() ? '1' : '0' }}">

    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-lg-down">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalEnsayoTitulo">
                    Ensayo · Folio <span id="ensayoFolio"></span> · Núcleo <span id="ensayoNumero"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form action="{{ route('ensayo.guardar') }}" method="POST" id="formEnsayo">
                @csrf
                <input type="hidden" name="nucleo_id" id="ensayoNucleoId">

                <div class="modal-body">

                    {{-- Encabezado --}}
                    <div class="row g-2 g-md-3 mb-3">
                        <div class="col-6 col-md-3">
                            <label class="form-label">Fecha de ensayo</label>
                            <input type="text" id="h-fecha" class="form-control bg-light" readonly>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Clave de obra</label>
                            <input type="text" id="h-clave-obra" class="form-control bg-light" readonly>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Obra</label>
                            <input type="text" id="h-obra" class="form-control bg-light" readonly>
                        </div>
                    </div>

                    <div class="hoja-titulo">TABLA DE RESULTADOS</div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle mb-0 hoja-tabla" id="tabla-ensayo">

                            {{-- Lo que falta capturar + sus cálculos (se llena desde JS) --}}
                            <tbody id="tbody-pendiente">
                                <tr class="hoja-seccion hoja-seccion-pend">
                                    <th colspan="2">Por capturar</th>
                                </tr>
                            </tbody>

                            {{-- Lo que ya estaba capturado + sus cálculos (se llena desde JS) --}}
                            <tbody id="tbody-capturado">
                                <tr class="hoja-seccion hoja-seccion-cap">
                                    <th colspan="2">Ya capturado</th>
                                </tr>

                                @foreach ($filas as $f)
                                <tr data-fila="{{ $f['k'] }}"
                                    data-dep="{{ implode(',', $f['dep'] ?? [$f['k']]) }}">
                                    <th scope="row" class="hoja-label">{{ $f['l'] }}</th>
                                    <td class="hoja-celda {{ in_array($f['t'], ['calc', 'info']) ? 'celda-calc' : '' }}">
                                        @if ($f['t'] === 'info')
                                        <span data-info="{{ $f['k'] }}">—</span>
                                        @elseif ($f['t'] === 'calc')
                                        <span data-calc="{{ $f['k'] }}">—</span>
                                        @elseif ($f['t'] === 'sel')
                                        <select name="{{ $f['k'] }}" class="form-select form-select-sm text-center" required>
                                            <option value="" selected disabled>Seleccione...</option>
                                            @foreach (['I', 'II', 'III', 'IV', 'V', 'VI'] as $tf)
                                            <option value="{{ $tf }}">{{ $tf }}</option>
                                            @endforeach
                                        </select>
                                        @else
                                        <input type="text" inputmode="decimal" autocomplete="off"
                                            class="form-control form-control-sm campo-num" required>
                                        <input type="hidden" name="{{ $f['k'] }}">
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>

                        </table>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-guardar" id="btn-guardar-ensayo">Guardar ensayo</button>
                </div>
            </form>

        </div>
    </div>
</div>

<script src="{{ asset('js/pages/Ensayo.js') }}"></script>

@endsection