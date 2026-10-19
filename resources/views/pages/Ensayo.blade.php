
@extends('plantilla')

@section('navbar-title', 'Ensayo')
@section('back-route', route('inicio'))

@section('contenido')
@include('layouts.navbar')

<style>
    .ensayo-contenedor {
        max-width: 2500px;
        width: 96%
    }

    .encabezado-ensayo {
    background: #17365d;
    color: #ffffff;
    padding: 25px 30px;
    border-radius: 12px;
    border-left: 7px solid #f28c28;
    box-shadow: 0 4px 12px rgba(20, 45, 75, 0.12);
}

.ensayo-titulo {
    color: #ffffff;
    font-weight: 700;
    margin-bottom: 8px;
}

.ensayo-subtitulo {
    color: #e2eaf5;
    font-size: 1rem;
}

    .nucleo-tarjeta {
        border: 1px solid #dce3eb;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 14px rgba(20, 45, 75, 0.07);
        transition: box-shadow 0.2s ease;
    }

    .nucleo-tarjeta:hover {
        box-shadow: 0 6px 18px rgba(20, 45, 75, 0.12);
    }

    .nucleo-encabezado {
        background: #ffffff;
        padding: 22px;
    }

    .nucleo-folio {
        color: #17365d;
        font-weight: 700;
        font-size: 1.15rem;
    }

    .nucleo-numero {
        display: inline-block;
        background: #eaf2fc;
        color: #1759a6;
        padding: 5px 12px;
        border-radius: 20px;
        font-weight: 700;
    }

    .dato-nucleo {
        margin-bottom: 12px;
    }

    .dato-nucleo strong {
        display: block;
        color: #6c757d;
        font-size: 0.82rem;
        font-weight: 600;
        margin-bottom: 3px;
    }

    .dato-nucleo span {
        color: #243447;
        font-weight: 500;
        overflow-wrap: anywhere;
    }

    .boton-ensayar {
        min-width: 150px;
        background: #1759a6;
        border-color: #1759a6;
        border-radius: 8px;
        padding: 10px 22px;
        font-weight: 600;
    }

    .boton-ensayar:hover {
        background: #10447f;
        border-color: #10447f;
    }

    .formulario-ensayo {
        border-top: 1px solid #dce3eb;
        background: #f8fafc;
        padding: 24px;
    }

    .seccion-formulario {
        color: #17365d;
        font-weight: 700;
        margin-bottom: 16px;
    }

    .resultado {
        background: #eaf2fc;
        font-weight: 600;
    }

    .tabla-medidas th {
        background: #eaf2fc;
        color: #17365d;
        white-space: nowrap;
    }

    .boton-guardar {
        background: #198754;
        border-color: #198754;
        padding: 10px 25px;
        border-radius: 8px;
        font-weight: 600;
    }

    .boton-guardar:hover {
        background: #146c43;
        border-color: #146c43;
    }

    [hidden] {
        display: none !important;
    }
</style>

<div class="container ensayo-contenedor mt-4 mb-5">

    <div class="encabezado-ensayo mb-4">
        <h1 class="ensayo-titulo">Ensayo de núcleos</h1>
        <p class="ensayo-subtitulo mb-0">
            Selecciona un núcleo pendiente para capturar los datos de su ensayo.
        </p>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info">
            {{ session('info') }}
        </div>
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

        @php
            $abrirFormulario = $errors->any()
                && (string) old('nucleo_id') === (string) $nucleo->id_seg;
        @endphp

        <div class="card nucleo-tarjeta mb-4">

            {{-- Información visible del núcleo --}}
            <div class="nucleo-encabezado">

                <div class="d-flex flex-wrap justify-content-between
                            align-items-start mb-4">

                    <div>
                        <div class="nucleo-folio mb-2">
                            Folio: {{ $nucleo->folio }}
                        </div>

                        <span class="nucleo-numero">
                            Núcleo {{ $nucleo->no_nucleo }}
                        </span>
                    </div>

                    <span class="badge bg-warning text-dark mt-2">
                        Pendiente
                    </span>

                </div>

                <div class="row">

                    <div class="col-md-6 col-lg-3 dato-nucleo">
                        <strong>Fecha de ensayo</strong>
                        <span>
                            {{ \Carbon\Carbon::parse($nucleo->fecha_ensayo)->format('d/m/Y') }}
                        </span>
                    </div>

                    <div class="col-md-6 col-lg-3 dato-nucleo">
                        <strong>Clave de obra</strong>
                        <span>{{ $nucleo->clave_obra }}</span>
                    </div>

                    <div class="col-md-6 col-lg-3 dato-nucleo">
                        <strong>Obra</strong>
                        <span>{{ $nucleo->obra }}</span>
                    </div>

                    <div class="col-md-6 col-lg-3 dato-nucleo">
                        <strong>Elemento</strong>
                        <span>{{ $nucleo->elemento }}</span>
                    </div>

                </div>

                <div class="text-end mt-3">
                    <button
                        type="button"
                        class="btn btn-primary boton-ensayar"
                        data-target="formulario-ensayo-{{ $nucleo->id_seg }}"
                        aria-expanded="{{ $abrirFormulario ? 'true' : 'false' }}">

                        {{ $abrirFormulario ? 'Cerrar formulario' : 'Ensayar' }}

                    </button>
                </div>

            </div>

            {{-- Formulario oculto hasta pulsar Ensayar --}}
            <div
                id="formulario-ensayo-{{ $nucleo->id_seg }}"
                class="formulario-ensayo"
                @if(!$abrirFormulario) hidden @endif>

                <form
                    action="{{ route('ensayo.guardar') }}"
                    method="POST"
                    class="form-ensayo"
                    data-masa="{{ $nucleo->masa_g }}"
                    data-d1="{{ $nucleo->diametro_1_mm }}"
                    data-d2="{{ $nucleo->diametro_2_mm }}"
                    data-s1="{{ $nucleo->altura_1_sin_cabeceo_mm }}"
                    data-s2="{{ $nucleo->altura_2_sin_cabeceo_mm }}"
                    data-c1="{{ $nucleo->altura_1_con_cabeceo_mm }}"
                    data-c2="{{ $nucleo->altura_2_con_cabeceo_mm }}">

                    @csrf

                    <input
                        type="hidden"
                        name="nucleo_id"
                        value="{{ $nucleo->id_seg }}">

                    {{-- Medidas registradas anteriormente --}}
                    <h5 class="seccion-formulario">
                        Datos y medidas del núcleo
                    </h5>

                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-sm
                                      align-middle tabla-medidas">

                            <thead>
                                <tr>
                                    <th>Masa (g)</th>
                                    <th>Diámetro 1 (mm)</th>
                                    <th>Diámetro 2 (mm)</th>
                                    <th>Altura sin cabeceo 1 (mm)</th>
                                    <th>Altura sin cabeceo 2 (mm)</th>
                                    <th>Altura con cabeceo 1 (mm)</th>
                                    <th>Altura con cabeceo 2 (mm)</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr>
                                    <td>{{ $nucleo->masa_g }}</td>
                                    <td>{{ $nucleo->diametro_1_mm }}</td>
                                    <td>{{ $nucleo->diametro_2_mm }}</td>
                                    <td>{{ $nucleo->altura_1_sin_cabeceo_mm }}</td>
                                    <td>{{ $nucleo->altura_2_sin_cabeceo_mm }}</td>
                                    <td>{{ $nucleo->altura_1_con_cabeceo_mm }}</td>
                                    <td>{{ $nucleo->altura_2_con_cabeceo_mm }}</td>
                                </tr>
                            </tbody>

                        </table>
                    </div>

                    {{-- Captura del ensayo --}}
                    <h5 class="seccion-formulario">
                        Captura del ensayo
                    </h5>

                    <div class="row g-3">

                        <div class="col-md-4">
                            <label class="form-label" for="carga-{{ $nucleo->id_seg }}">
                                Carga aplicada (kN)
                            </label>

                            <input
                                id="carga-{{ $nucleo->id_seg }}"
                                type="number"
                                name="carga_kn"
                                class="form-control carga-kn"
                                min="0.0001"
                                max="999999.9999"
                                step="0.0001"
                                value="{{ old('nucleo_id') == $nucleo->id_seg ? old('carga_kn') : $nucleo->carga_kn }}"
                                placeholder="Ej. 250.5000"
                                required>

                            <small class="text-muted">
                                Introduce la carga registrada durante el ensayo.
                            </small>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label" for="falla-{{ $nucleo->id_seg }}">
                                Tipo de falla
                            </label>

                            <textarea
                                id="falla-{{ $nucleo->id_seg }}"
                                name="tipo_falla"
                                class="form-control"
                                rows="2"
                                maxlength="50"
                                placeholder="Describe el tipo de falla del núcleo"
                                required>{{ old('nucleo_id') == $nucleo->id_seg ? old('tipo_falla') : $nucleo->tipo_falla }}</textarea>
                        </div>

                    </div>

                    <hr class="my-4">

                    {{-- Resultados calculados --}}
                    <h5 class="seccion-formulario">
                        Resultados calculados automáticamente
                    </h5>

                    <div class="row g-3">

                        <div class="col-md-4">
                            <label class="form-label">Área (cm²)</label>
                            <input
                                type="text"
                                class="form-control resultado"
                                data-resultado="area"
                                readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Carga (kgf)</label>
                            <input
                                type="text"
                                class="form-control resultado"
                                data-resultado="kg"
                                readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Esfuerzo directo (kg/cm²)</label>
                            <input
                                type="text"
                                class="form-control resultado"
                                data-resultado="directo"
                                readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Esfuerzo real (kg/cm²)</label>
                            <input
                                type="text"
                                class="form-control resultado"
                                data-resultado="real"
                                readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Esfuerzo real (MPa)</label>
                            <input
                                type="text"
                                class="form-control resultado"
                                data-resultado="mpa"
                                readonly>
                        </div>

                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-success boton-guardar">
                            Guardar ensayo
                        </button>
                    </div>

                </form>

            </div>

        </div>

    @empty

        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <h5 class="text-success">No hay núcleos pendientes</h5>
                <p class="text-muted mb-0">
                    Todos los núcleos disponibles ya tienen su ensayo completo.
                </p>
            </div>
        </div>

    @endforelse

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const PI_XLS = 3.1416;
    const KGF_POR_KN = 101.972;
    const DIV_MPA = 10.1972;

    // Abrir y cerrar el formulario de cada núcleo.
    document.querySelectorAll('.boton-ensayar').forEach(function (boton) {

        boton.addEventListener('click', function () {

            const idFormulario = boton.dataset.target;
            const formulario = document.getElementById(idFormulario);

            if (!formulario) return;

            const estaOculto = formulario.hidden;

            formulario.hidden = !estaOculto;
            boton.setAttribute('aria-expanded', String(estaOculto));

            boton.textContent = estaOculto
                ? 'Cerrar formulario'
                : 'Ensayar';

            if (estaOculto) {
                formulario.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    function numero(valor) {
        const n = parseFloat(valor);
        return Number.isFinite(n) ? n : null;
    }

    function promedio(a, b) {
        return a !== null && b !== null && a > 0 && b > 0
            ? (a + b) / 2
            : null;
    }

    function factorCorreccion(e) {
        if (e === null) return null;
        if (e > 1.75) return 1;

        return (
            0.2133 * Math.pow(e, 3)
            - 1.04 * Math.pow(e, 2)
            + 1.7667 * e
        ) - 0.07;
    }

    function mostrar(form, campo, valor) {
        const input = form.querySelector(
            '[data-resultado="' + campo + '"]'
        );

        if (!input) return;

        input.value = valor === null || !Number.isFinite(valor)
            ? ''
            : valor.toFixed(4);
    }

    // Calcular los resultados al escribir la carga.
    document.querySelectorAll('.form-ensayo').forEach(function (form) {

        const cargaInput = form.querySelector('.carga-kn');

        function calcular() {
            const d1 = numero(form.dataset.d1);
            const d2 = numero(form.dataset.d2);
            const s1 = numero(form.dataset.s1);
            const s2 = numero(form.dataset.s2);
            const c1 = numero(form.dataset.c1);
            const c2 = numero(form.dataset.c2);
            const kn = numero(cargaInput.value);

            const dProm = promedio(d1, d2);
            const hSin = promedio(s1, s2);
            const hCon = promedio(c1, c2);

            const area = dProm !== null
                ? (dProm * dProm * PI_XLS / 4) / 100
                : null;

            const esbeltez = hCon !== null && dProm !== null
                ? hCon / dProm
                : null;

            const factor = factorCorreccion(esbeltez);

            const kg = kn !== null && kn > 0
                ? kn * KGF_POR_KN
                : null;

            const directo = kg !== null && area !== null && area > 0
                ? kg / area
                : null;

            const real = directo !== null && factor !== null
                ? directo * factor
                : null;

            const mpa = real !== null
                ? real / DIV_MPA
                : null;

            mostrar(form, 'area', area);
            mostrar(form, 'kg', kg);
            mostrar(form, 'directo', directo);
            mostrar(form, 'real', real);
            mostrar(form, 'mpa', mpa);
        }

        cargaInput.addEventListener('input', calcular);
        calcular();
    });

});
</script>

@endsection
