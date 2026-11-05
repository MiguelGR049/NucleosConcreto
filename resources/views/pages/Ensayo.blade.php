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
                    data-obra="{{ $nucleo->obra }}"
                    data-masa="{{ $nucleo->masa_g }}"
                    data-d1="{{ $nucleo->diametro_1_mm }}"
                    data-d2="{{ $nucleo->diametro_2_mm }}"
                    data-s1="{{ $nucleo->altura_1_sin_cabeceo_mm }}"
                    data-s2="{{ $nucleo->altura_2_sin_cabeceo_mm }}"
                    data-c1="{{ $nucleo->altura_1_con_cabeceo_mm }}"
                    data-c2="{{ $nucleo->altura_2_con_cabeceo_mm }}"
                    data-carga="{{ $nucleo->carga_kn }}"
                    data-falla="{{ $nucleo->tipo_falla }}">
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
<div
    class="modal fade hoja-modal ensayo-modal" id="modalEnsayo" tabindex="-1" aria-labelledby="modalEnsayoTitulo" aria-hidden="true" data-old-nucleo="{{ old('nucleo_id') }}" data-old-carga="{{ old('carga_kn') }}" data-old-falla="{{ old('tipo_falla') }}" data-hay-errores="{{ $errors->any() ? '1' : '0' }}">

    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable modal-fullscreen-md-down">
        <div class="modal-content">

            <form action="{{ route('ensayo.guardar') }}" method="POST" id="formEnsayo">
                @csrf
                <input type="hidden" name="nucleo_id" id="ensayoNucleoId">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalEnsayoTitulo">
                        Ensayo · Folio <span id="ensayoFolio"></span> · Núcleo <span id="ensayoNumero"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">

                    <h6 class="ensayo-modal-seccion">Datos y medidas del núcleo</h6>

                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-sm align-middle tabla-medidas mb-0">
                            <thead>
                                <tr>
                                    <th>Masa (g)</th>
                                    <th>Diámetro 1 (mm)</th>
                                    <th>Diámetro 2 (mm)</th>
                                    <th>Alt. sin cabeceo 1 (mm)</th>
                                    <th>Alt. sin cabeceo 2 (mm)</th>
                                    <th>Alt. con cabeceo 1 (mm)</th>
                                    <th>Alt. con cabeceo 2 (mm)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td data-medida="masa"></td>
                                    <td data-medida="d1"></td>
                                    <td data-medida="d2"></td>
                                    <td data-medida="s1"></td>
                                    <td data-medida="s2"></td>
                                    <td data-medida="c1"></td>
                                    <td data-medida="c2"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h6 class="ensayo-modal-seccion">Captura del ensayo</h6>

                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label" for="ensayoCarga">Carga aplicada (kN)</label>
                            <input id="ensayoCarga" type="number" name="carga_kn" class="form-control" min="0.0001" max="999999.9999" step="0.0001" placeholder="Ej. 250.5000" required>
                            <small class="text-muted">Carga registrada durante el ensayo.</small>
                        </div>

                        <div class="col-12 col-md-8">
                            <label class="form-label" for="ensayoFalla">Tipo de falla</label>
                            <input id="ensayoFalla" name="tipo_falla" class="form-control" rows="2" maxlength="50" placeholder="Describe el tipo de falla del núcleo" required></input>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h6 class="ensayo-modal-seccion">Resultados calculados automáticamente</h6>

                    <div class="row g-3">
                        <div class="col-12 col-sm-6 col-lg-4">
                            <label class="form-label">Área (cm²)</label>
                            <input type="text" class="form-control resultado" data-resultado="area" readonly>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-4">
                            <label class="form-label">Carga (kgf)</label>
                            <input type="text" class="form-control resultado" data-resultado="kg" readonly>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-4">
                            <label class="form-label">Esfuerzo directo (kg/cm²)</label>
                            <input type="text" class="form-control resultado" data-resultado="directo" readonly>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label">Esfuerzo real (kg/cm²)</label>
                            <input type="text" class="form-control resultado" data-resultado="real" readonly>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label">Esfuerzo real (MPa)</label>
                            <input type="text" class="form-control resultado" data-resultado="mpa" readonly>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-guardar">Guardar ensayo</button>
                </div>

            </form>

        </div>
    </div>
</div>

<script src="{{ asset('js/pages/Ensayo.js') }}"></script>

@endsection