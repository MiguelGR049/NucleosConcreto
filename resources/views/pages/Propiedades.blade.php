@extends('plantilla')
@section('contenido')

@section('navbar-title', 'Captura de propiedades')
@section('back-route', route('inicio'))

@include('layouts.navbar')

@php
    // k = clave | l = etiqueta | t = texto, num, info (solo lectura) o calc (se calcula) | d = decimales
    $filas = [
        ['k' => 'no_nucleo',                'l' => 'No. de muestra / núcleo',       't' => 'texto'],
        ['k' => 'elemento',                 'l' => 'Elemento',                      't' => 'info'],
        ['k' => 'localizacion',             'l' => 'Localización',                  't' => 'info'],
        ['k' => 'dia',                      'l' => 'Edad de ensayo días',           't' => 'info'],
        ['k' => 'masa_g',                   'l' => 'Masa del espécimen g',          't' => 'num'],
        ['k' => 'masa_unitaria',            'l' => 'Masa unitaria kg/m³',           't' => 'calc', 'd' => 2],
        ['k' => 'diametro_1_mm',            'l' => 'Diámetro 1 mm',                 't' => 'num'],
        ['k' => 'diametro_2_mm',            'l' => 'Diámetro 2 mm',                 't' => 'num'],
        ['k' => 'diametro_prom',            'l' => 'Diámetro promedio mm',          't' => 'calc', 'd' => 2],
        ['k' => 'altura_1_sin_cabeceo_mm',  'l' => 'Altura 1 sin cabeceo mm',       't' => 'num'],
        ['k' => 'altura_2_sin_cabeceo_mm',  'l' => 'Altura 2 sin cabeceo mm',       't' => 'num'],
        ['k' => 'altura_prom_sin',          'l' => 'Altura promedio sin cabeceo mm','t' => 'calc', 'd' => 2],
        ['k' => 'altura_1_con_cabeceo_mm',  'l' => 'Altura 1 con cabeceo mm',       't' => 'num'],
        ['k' => 'altura_2_con_cabeceo_mm',  'l' => 'Altura 2 con cabeceo mm',       't' => 'num'],
        ['k' => 'altura_prom_con',          'l' => 'Altura promedio con cabeceo mm','t' => 'calc', 'd' => 2],
        ['k' => 'esbeltez',                 'l' => 'Esbeltez (h/d)',                't' => 'calc', 'd' => 2],
        ['k' => 'factor',                   'l' => 'Factor de corrección',          't' => 'calc', 'd' => 2],
        ['k' => 'area',                     'l' => 'Área cm²',                      't' => 'calc', 'd' => 2],
        ['k' => 'carga_kn',                 'l' => 'Carga kN',                      't' => 'num'],
        ['k' => 'carga_kg',                 'l' => 'Carga kg',                      't' => 'calc', 'd' => 2],
        ['k' => 'tipo_falla',               'l' => 'Tipo de falla',                 't' => 'texto'],
        ['k' => 'esf_directo',              'l' => 'Esfuerzo directo kg/cm²',       't' => 'calc', 'd' => 2],
        ['k' => 'esf_real',                 'l' => 'Esfuerzo real kg/cm²',          't' => 'calc', 'd' => 2],
        ['k' => 'esf_real_mpa',             'l' => 'Esfuerzo real MPa',             't' => 'calc', 'd' => 2],
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
                                            data-elemento="{{ $m->elemento }}"
                                            data-localizacion="{{ $m->localizacion }}"
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
                                    <td class="hoja-celda {{ $f['t'] === 'calc' ? 'celda-calc' : '' }} d-none" data-col="{{ $c }}">
                                        @if ($f['t'] === 'info')
                                            <span data-info="{{ $f['k'] }}">—</span>
                                        @elseif ($f['t'] === 'calc')
                                            <span data-calc="{{ $f['k'] }}" data-dec="{{ $f['d'] }}">—</span>
                                        @elseif ($f['t'] === 'texto')
                                            <input type="text" class="form-control form-control-sm"
                                                name="nucleos[{{ $c }}][{{ $f['k'] }}]"
                                                maxlength="{{ $f['k'] === 'tipo_falla' ? 50 : 20 }}" disabled>
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

<script>
document.addEventListener('DOMContentLoaded', function () {

    const aviso      = document.getElementById('aviso-captura');
    const modalEl    = document.getElementById('modalHoja');
    const formHoja   = document.getElementById('formHoja');
    const btnGuardar = document.getElementById('btn-guardar-hoja');
    const btnAgregar = document.getElementById('btn-agregar-nucleo');
    const btnQuitar  = document.getElementById('btn-quitar-nucleo');
    const contador   = document.getElementById('contador-nucleos');
    const infoCap    = document.getElementById('info-capacidad');

    const MAX_COLUMNAS = 4;          // columnas que tiene la hoja
    const DEC_VISTA    = 2;          // decimales que se muestran
    const DEC_REAL     = 4;          // decimales que se conservan (igual que la base de datos)
    const PI_XLS       = 3.1416;     // el mismo valor que usa el Excel
    const KGF_POR_KN   = 101.972;    // kN -> kg
    const DIV_MPA      = 10.1972;    // kg/cm² -> MPa

    let maxPermitido = 1;            // tope para la fecha elegida (lo calcula el servidor)
    let nColumnas    = 1;            // columnas visibles ahora

    function avisar(msg) {
        aviso.textContent = msg;
        aviso.classList.remove('d-none');
        aviso.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    /* ---------- Redondeo estilo Excel (sobre el número decimal, no el binario) ---------- */
    function redondear(n, dec) {
        const r = Number(Math.round(n + 'e' + dec) + 'e-' + dec);
        return isFinite(r) ? r : n;
    }

    /* ---------- Campos numéricos: vista a 2 decimales, valor real oculto ---------- */
    // El input oculto (el que se envía) va justo después del visible
    function sincronizarNumero(t) {
        let txt = t.value;

        // Coma o punto como separador decimal
        txt = txt.includes('.') ? txt.replace(/,/g, '') : txt.replace(',', '.');
        txt = txt.replace(/[^0-9.]/g, '');

        const punto = txt.indexOf('.');
        if (punto !== -1) {
            const entero    = txt.slice(0, punto);
            const decimales = txt.slice(punto + 1).replace(/\./g, '').slice(0, DEC_REAL);
            txt = entero + '.' + decimales;
        }

        if (txt !== t.value) t.value = txt;

        let real = txt.replace(/\.$/, '').replace(/^\./, '0.');
        t.nextElementSibling.value = real;
    }

    function formatearCampo(t) {
        const oculto = t.nextElementSibling;
        if (oculto.value === '') { t.value = ''; return; }
        t.value = redondear(parseFloat(oculto.value), DEC_VISTA).toFixed(DEC_VISTA);
    }

    // Al entrar al campo se ve el valor completo para poder editarlo
    formHoja.addEventListener('focusin', function (e) {
        if (e.target.classList.contains('campo-num')) {
            e.target.value = e.target.nextElementSibling.value;
        }
    });

    // Al salir se muestra redondeado a 2 decimales
    formHoja.addEventListener('focusout', function (e) {
        if (e.target.classList.contains('campo-num')) {
            formatearCampo(e.target);
        }
    });

    // Al escribir: se guarda el valor real y se recalcula la columna
    formHoja.addEventListener('input', function (e) {
        if (e.target.classList.contains('campo-num')) {
            sincronizarNumero(e.target);
        }
        const td = e.target.closest('[data-col]');
        if (td) calcular(parseInt(td.dataset.col, 10));
    });

    /* ---------- Selección única en la tabla ---------- */
    const checks = document.querySelectorAll('.chk-ensayar');
    checks.forEach(function (chk) {
        chk.addEventListener('change', function () {
            if (!chk.checked) return;
            checks.forEach(function (otro) {
                if (otro !== chk && !otro.disabled) otro.checked = false;
            });
        });
    });

    /* ---------- Abrir el modal ---------- */
    document.getElementById('btn-captura').addEventListener('click', function () {
        aviso.classList.add('d-none');

        const sel = document.querySelector('.chk-ensayar:checked:not(:disabled)');

        if (!sel) {
            return avisar('Selecciona la muestra que vas a capturar.');
        }
        if (parseInt(sel.dataset.max, 10) < 1) {
            return avisar('Esta recepción ya no tiene núcleos disponibles para ensayar.');
        }

        abrirModal(sel);
    });

    function abrirModal(chk) {
        const d = chk.dataset;

        formHoja.reset();
        btnGuardar.disabled = false;

        // reset() no limpia los inputs ocultos, se limpian a mano
        formHoja.querySelectorAll('.campo-num').forEach(function (t) {
            t.value = '';
            t.nextElementSibling.value = '';
        });

        document.getElementById('h-fecha-ensayo-id').value = d.id;
        document.getElementById('h-clave-obra').value = d.claveObra || '';
        document.getElementById('h-obra').value       = d.obra || '';
        document.getElementById('h-extraccion').value = d.extraccion || '';
        document.getElementById('h-datos').value      = d.datos || '';

        maxPermitido = Math.min(parseInt(d.max, 10), MAX_COLUMNAS);

        infoCap.textContent =
            'Quedan ' + d.restantes + ' núcleo(s) por ensayar en esta recepción y ' +
            d.pendientes + ' fecha(s) de ensayo pendiente(s). ' +
            'En esta fecha puedes capturar hasta ' + maxPermitido + '.';

        // Elemento, localización y edad son los mismos en todas las columnas
        for (let c = 0; c < MAX_COLUMNAS; c++) {
            setInfo(c, 'elemento',     d.elemento);
            setInfo(c, 'localizacion', d.localizacion);
            setInfo(c, 'dia',          d.dia);
        }

        activarColumnas(1);
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    /* ---------- Columnas (núcleos) ---------- */
    function activarColumnas(n) {
        nColumnas = n;

        for (let c = 0; c < MAX_COLUMNAS; c++) {
            const activa = c < n;

            document.querySelectorAll('#tabla-hoja [data-col="' + c + '"]').forEach(function (td) {
                td.classList.toggle('d-none', !activa);
                td.querySelectorAll('input').forEach(function (i) {
                    i.disabled = !activa;
                    i.required = activa;
                    if (!activa) i.value = '';
                });
            });

            calcular(c);
        }

        contador.textContent = 'Núcleos: ' + n + ' de ' + maxPermitido;
        btnAgregar.disabled  = n >= maxPermitido;
        btnQuitar.disabled   = n <= 1;
    }

    btnAgregar.addEventListener('click', function () {
        if (nColumnas >= maxPermitido) return;
        activarColumnas(nColumnas + 1);

        const nuevo = document.querySelector('[name="nucleos[' + (nColumnas - 1) + '][no_nucleo]"]');
        if (nuevo) nuevo.focus();
    });

    btnQuitar.addEventListener('click', function () {
        if (nColumnas <= 1) return;

        const ultima = nColumnas - 1;
        const conDatos = Array.from(
            document.querySelectorAll('#tabla-hoja [data-col="' + ultima + '"] input')
        ).some(function (i) { return i.value.trim() !== ''; });

        if (conDatos && !confirm('¿Quitar el último núcleo? Se perderán los datos que capturaste en esa columna.')) {
            return;
        }
        activarColumnas(nColumnas - 1);
    });

    /* ---------- Helpers ---------- */
    function setInfo(c, k, texto) {
        const el = document.querySelector('[data-col="' + c + '"] [data-info="' + k + '"]');
        if (el) el.textContent = texto || '—';
    }

    // Lee el valor REAL (el input oculto lleva el name)
    function valor(c, k) {
        const el = document.querySelector('[name="nucleos[' + c + '][' + k + ']"]');
        if (!el || el.value.trim() === '') return null;
        const n = parseFloat(el.value);
        return isNaN(n) ? null : n;
    }

    function setCalc(c, k, n) {
        const el = document.querySelector('[data-col="' + c + '"] [data-calc="' + k + '"]');
        if (!el) return;
        if (n === null || !isFinite(n)) { el.textContent = '—'; return; }
        const dec = parseInt(el.dataset.dec, 10);
        el.textContent = redondear(n, dec).toLocaleString('es-MX', {
            minimumFractionDigits: dec,
            maximumFractionDigits: dec
        });
    }

    // Igual que la fila 22 del Excel
    function factorCorreccion(e) {
        if (e === null) return null;
        if (e > 1.75) return 1;
        return (0.2133 * Math.pow(e, 3) - 1.04 * Math.pow(e, 2) + 1.7667 * e) - 0.07;
    }

    /* ---------- Fórmulas (una columna) ---------- */
    function calcular(c) {
        const masa = valor(c, 'masa_g');
        const d1 = valor(c, 'diametro_1_mm'),            d2 = valor(c, 'diametro_2_mm');
        const s1 = valor(c, 'altura_1_sin_cabeceo_mm'),  s2 = valor(c, 'altura_2_sin_cabeceo_mm');
        const c1 = valor(c, 'altura_1_con_cabeceo_mm'),  c2 = valor(c, 'altura_2_con_cabeceo_mm');
        const kn = valor(c, 'carga_kn');

        // En el Excel, una celda vacía o en 0 deja el resultado en blanco.
        const prom = (a, b) => (a && b !== null) ? (a + b) / 2 : null;

        const dProm = prom(d1, d2);    // fila 14
        const hSin  = prom(s1, s2);    // fila 17
        const hCon  = prom(c1, c2);    // fila 20

        // fila 23: (d² · 3.1416 / 4) / 100
        const area = (s1 > 0 && dProm !== null)
            ? (dProm * dProm * PI_XLS / 4) / 100
            : null;

        // fila 11: (masa / (altura sin cabeceo · área)) · 10000
        const masaUnit = (masa && hSin && area)
            ? (masa / (hSin * area)) * 10000
            : null;

        // fila 21: altura con cabeceo / diámetro promedio
        const esbeltez = (s1 && hCon !== null && dProm)
            ? hCon / dProm
            : null;

        const factor = factorCorreccion(esbeltez);   // fila 22

        // filas 25, 27, 28, 29
        const kg         = kn ? kn * KGF_POR_KN : null;
        const esfDirecto = (kg !== null && area) ? kg / area : null;
        const esfReal    = (esfDirecto !== null && factor !== null) ? esfDirecto * factor : null;
        const esfMpa     = esfReal !== null ? esfReal / DIV_MPA : null;

        setCalc(c, 'masa_unitaria',   masaUnit);
        setCalc(c, 'diametro_prom',   dProm);
        setCalc(c, 'altura_prom_sin', hSin);
        setCalc(c, 'altura_prom_con', hCon);
        setCalc(c, 'esbeltez',        esbeltez);
        setCalc(c, 'factor',          factor);
        setCalc(c, 'area',            area);
        setCalc(c, 'carga_kg',        kg);
        setCalc(c, 'esf_directo',     esfDirecto);
        setCalc(c, 'esf_real',        esfReal);
        setCalc(c, 'esf_real_mpa',    esfMpa);
    }

    /* ---------- Envío ---------- */
    formHoja.addEventListener('submit', function (e) {
        // Un campo obligatorio con texto inválido (por ejemplo solo ".") no tiene valor real
        const invalido = Array.from(formHoja.querySelectorAll('.campo-num:not(:disabled)')).find(function (t) {
            return t.required && t.nextElementSibling.value === '';
        });

        if (invalido) {
            e.preventDefault();
            invalido.setCustomValidity('Escribe un número válido');
            invalido.reportValidity();
            invalido.setCustomValidity('');
            return;
        }

        btnGuardar.disabled = true;   // evita doble envío
    });
});
</script>

@endsection