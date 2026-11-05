document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('formDocumento');
    const tabla = document.getElementById('mdTabla');
    const inputResistencia = document.getElementById('mdResistencia');
    const selectMuestreadoPor = document.getElementById('mdMuestreadoPor');
    const inputFechaInforme = document.getElementById('mdFechaInforme');

    const DEC_VISTA = 2;
    const PI_XLS = 3.1416;      // el mismo valor que usa el Excel
    const KGF_POR_KN = 101.972; // kN -> kg
    const DIV_MPA = 10.1972;    // kg/cm² -> MPa
    const POR_PAGINA = 4;       // núcleos por hoja del PDF

    let recepcionActual = null; // recepción abierta en el modal
    let calcActual = [];        // cálculos de cada núcleo de esa recepción

    /* ---------- Helpers ---------- */
    // Redondeo estilo Excel (solo para mostrar; los cálculos usan el valor completo)
    function redondear(n, dec) {
        const r = Number(Math.round(n + 'e' + dec) + 'e-' + dec);
        return isFinite(r) ? r : n;
    }

    // Valor de la BD (viene como texto o null) -> número o null
    function val(v) {
        if (v === null || v === undefined || v === '') return null;
        const n = parseFloat(v);
        return isNaN(n) ? null : n;
    }

    function fmt(n, dec = DEC_VISTA) {
        if (n === null || n === undefined || !isFinite(n)) return '';
        return redondear(n, dec).toLocaleString('es-MX', {
            minimumFractionDigits: dec,
            maximumFractionDigits: dec
        });
    }

    // Para el PDF: sin separador de miles, como en el Excel
    function fmtPdf(n, dec) {
        if (n === null || n === undefined || !isFinite(n)) return '';
        return redondear(n, dec).toFixed(dec);
    }

    const fecha = f => {
        if (!f || String(f).startsWith('0000')) return '';
        const [a, m, d] = String(f).substring(0, 10).split('-');
        return `${d}/${m}/${a}`;
    };

    // Fechas del PDF: iso = 2026-09-12 | barras = 2026/09/12
    const fechaIso = f => (!f || String(f).startsWith('0000')) ? '' : String(f).substring(0, 10);
    const fechaBarras = f => fechaIso(f).replace(/-/g, '/');

    // Días entre dos fechas (YYYY-MM-DD); null si falta alguna
    function diasEntre(fin, ini) {
        const aMs = s => {
            if (!s || String(s).startsWith('0000')) return null;
            const [a, m, d] = String(s).substring(0, 10).split('-').map(Number);
            const ms = Date.UTC(a, m - 1, d);
            return isNaN(ms) ? null : ms;
        };
        const f = aMs(fin), i = aMs(ini);
        return (f === null || i === null) ? null : Math.round((f - i) / 86400000);
    }

    const poner = (id, v) => { document.getElementById(id).value = v ?? ''; };
    const leer = id => document.getElementById(id).value;

    /* ---------- Fórmulas (las mismas de Propiedades.js) ---------- */
    function factorCorreccion(e) {
        if (e === null) return null;
        if (e > 1.75) return 1;
        return (0.2133 * Math.pow(e, 3) - 1.04 * Math.pow(e, 2) + 1.7667 * e) - 0.07;
    }

    function calcular(n) {
        const masa = val(n.masa_g);
        const d1 = val(n.diametro_1_mm), d2 = val(n.diametro_2_mm);
        const s1 = val(n.altura_1_sin_cabeceo_mm), s2 = val(n.altura_2_sin_cabeceo_mm);
        const c1 = val(n.altura_1_con_cabeceo_mm), c2 = val(n.altura_2_con_cabeceo_mm);
        const kn = val(n.carga_kn);

        // En el Excel, una celda vacía o en 0 deja el resultado en blanco
        const prom = (a, b) => (a && b !== null) ? (a + b) / 2 : null;

        const dProm = prom(d1, d2);
        const hSin = prom(s1, s2);
        const hCon = prom(c1, c2);

        const area = (s1 > 0 && dProm !== null)
            ? (dProm * dProm * PI_XLS / 4) / 100
            : null;

        const masaUnit = (masa && hSin && area)
            ? (masa / (hSin * area)) * 10000
            : null;

        const esbeltez = (s1 && hCon !== null && dProm)
            ? hCon / dProm
            : null;

        const factor = factorCorreccion(esbeltez);

        const kg = kn ? kn * KGF_POR_KN : null;
        const esfDirecto = (kg !== null && area) ? kg / area : null;
        const esfReal = (esfDirecto !== null && factor !== null) ? esfDirecto * factor : null;
        const esfMpa = esfReal !== null ? esfReal / DIV_MPA : null;

        // =SI(carga kN=0,"",0.000004*real^2 + 0.00004*real + 0.0193)
        const u = (kn && esfReal !== null)
            ? 0.000004 * Math.pow(esfReal, 2) + 0.00004 * esfReal + 0.0193
            : null;

        return { dProm, hSin, hCon, area, masaUnit, esbeltez, factor, kg, esfDirecto, esfReal, esfMpa, u };
    }

    /* ---------- Declaración de conformidad y observaciones ---------- */
    // Excel: promedio(esfuerzo real) - promedio(U) >= 0.85 * resistencia
    //   y    mínimo de (esfuerzo real - U) por núcleo >= 0.75 * resistencia
    function declaracionConformidad(calc, claveObra, resistencia) {
        if (!claveObra) return '';                       // =SI(I2=0,"",...)
        if (!resistencia || resistencia <= 0) return ''; // sin resistencia de proyecto no se declara nada

        // Solo los núcleos ya ensayados (con esfuerzo real y U)
        const difs = calc
            .filter(c => c.esfReal !== null && c.u !== null)
            .map(c => c.esfReal - c.u);
        if (difs.length === 0) return '';

        const promedio = difs.reduce((a, b) => a + b, 0) / difs.length;
        const minimo = Math.min.apply(null, difs);

        const cumple = promedio >= resistencia * 0.85 && minimo >= resistencia * 0.75;

        return cumple
            ? 'LOS NÚCLEOS CUMPLEN CON LA RESISTENCIA ESPECIFICADA'
            : 'LOS NÚCLEOS NO CUMPLEN CON LA RESISTENCIA ESPECIFICADA';
    }

    function observacionesHoja(muestreadoPor) {
        return muestreadoPor === 'CLIENTE'
            ? 'LA MUESTRA ES PROPORCIONADA POR EL CLIENTE, LIEC SOLO SE RESPONSABILIZA DEL ENSAYO.'
            : '---';
    }

    function actualizarPie() {
        if (!recepcionActual) return;

        const campo = document.getElementById('mdConformidad');
        const resistencia = parseFloat(inputResistencia.value);
        const ensayados = calcActual.filter(c => c.esfReal !== null && c.u !== null).length;

        campo.value = declaracionConformidad(calcActual, recepcionActual.clave_obra, resistencia);

        // Si queda vacío, el placeholder explica por qué
        campo.placeholder = !resistencia
            ? 'Captura la resistencia de proyecto para ver la declaración.'
            : ensayados === 0
                ? 'Aún no hay núcleos ensayados (falta la carga en kN).'
                : '';

        poner('mdObservHoja', observacionesHoja(selectMuestreadoPor.value));
    }

    inputResistencia.addEventListener('input', actualizarPie);
    selectMuestreadoPor.addEventListener('change', actualizarPie);

    // La edad de ensaye depende de la fecha de informe
    inputFechaInforme.addEventListener('change', function () {
        if (recepcionActual) pintarTabla(recepcionActual, calcActual);
    });

    /* ---------- Filas de la tabla del modal (c: true = calculada, con trama) ---------- */
    const filas = [
        { t: 'No. de muestra / núcleo', v: (n, c, i) => n.no_nucleo || (i + 1) },
        { t: 'Elemento', v: (n, c, i, r) => n.elemento },
        { t: 'Localización', v: (n, c, i, r) => n.localizacion },
        { t: 'Fecha de colado', v: (n, c, i, r) => fecha(r.fecha_colado) },
        { t: 'Espesor, mm', v: n => fmt(val(n.espesor)) },
        { t: 'Edad de ensaye, días', v: (n, c, i, r) => diasEntre(inputFechaInforme.value, r.fecha_colado) ?? '', c: true },
        { t: 'Masa del espécimen, g', v: n => fmt(val(n.masa_g)) },
        { t: 'Masa volumétrica, kg·m⁻³', v: (n, c) => fmt(c.masaUnit), c: true },
        { t: 'Diámetro 1, mm', v: n => fmt(val(n.diametro_1_mm)) },
        { t: 'Diámetro 2, mm', v: n => fmt(val(n.diametro_2_mm)) },
        { t: 'Diámetro promedio, mm', v: (n, c) => fmt(c.dProm), c: true },
        { t: 'Altura 1 sin cabeceo, mm', v: n => fmt(val(n.altura_1_sin_cabeceo_mm)) },
        { t: 'Altura 2 sin cabeceo, mm', v: n => fmt(val(n.altura_2_sin_cabeceo_mm)) },
        { t: 'Altura promedio sin cabeceo, mm', v: (n, c) => fmt(c.hSin), c: true },
        { t: 'Altura 1 con cabeceo, mm', v: n => fmt(val(n.altura_1_con_cabeceo_mm)) },
        { t: 'Altura 2 con cabeceo, mm', v: n => fmt(val(n.altura_2_con_cabeceo_mm)) },
        { t: 'Altura promedio con cabeceo, mm', v: (n, c) => fmt(c.hCon), c: true },
        { t: 'Relación de esbeltez, h/d', v: (n, c) => fmt(c.esbeltez), c: true },
        { t: 'Factor de corrección', v: (n, c) => fmt(c.factor), c: true },
        { t: 'Área, cm²', v: (n, c) => fmt(c.area), c: true },
        { t: 'Carga, kN', v: n => fmt(val(n.carga_kn)) },
        { t: 'Carga, kg', v: (n, c) => fmt(c.kg), c: true },
        { t: 'Tipo de falla', v: n => n.tipo_falla ?? '' },
        { t: 'Esfuerzo directo, kg·cm⁻²', v: (n, c) => fmt(c.esfDirecto), c: true },
        { t: 'Esfuerzo real, kg·cm⁻²', v: (n, c) => fmt(c.esfReal), c: true },
        { t: 'Esfuerzo real, MPa', v: (n, c) => fmt(c.esfMpa), c: true },
        { t: 'U (±), MPa', v: (n, c) => fmt(c.u), c: true },
    ];

    function pintarTabla(r, calc) {
        tabla.innerHTML = '';
        const nucleos = r.nucleos || [];

        if (nucleos.length === 0) {
            const td = tabla.insertRow().insertCell();
            td.textContent = 'Aún no hay núcleos capturados para esta recepción.';
            td.className = 'text-muted';
            return;
        }

        filas.forEach(function (f) {
            const tr = tabla.insertRow();
            if (f.c) tr.classList.add('fila-calculada');

            const th = document.createElement('th');
            th.scope = 'row';
            th.textContent = f.t;
            tr.appendChild(th);

            nucleos.forEach(function (n, i) {
                tr.insertCell().textContent = f.v(n, calc[i], i, r) ?? '';
            });
        });
    }

    /* ---------- Datos para el PDF ---------- */
    // Los decimales de cada fila son los del formato CON-LAB-07
    function nucleoPdf(n, c, i, r, edad) {
        return {
            no_nucleo: n.no_nucleo || (i + 1),
            elemento: n.elemento ?? '',
            localizacion: n.localizacion ?? '',
            fecha_colado: fechaBarras(r.fecha_colado),
            espesor: fmtPdf(val(n.espesor), 2),
            edad: edad ?? '',
            masa: fmtPdf(val(n.masa_g), 0),
            masa_vol: fmtPdf(c.masaUnit, 0),
            d1: fmtPdf(val(n.diametro_1_mm), 2),
            d2: fmtPdf(val(n.diametro_2_mm), 2),
            dprom: fmtPdf(c.dProm, 1),
            a1s: fmtPdf(val(n.altura_1_sin_cabeceo_mm), 2),
            a2s: fmtPdf(val(n.altura_2_sin_cabeceo_mm), 2),
            apsin: fmtPdf(c.hSin, 1),
            a1c: fmtPdf(val(n.altura_1_con_cabeceo_mm), 0),
            a2c: fmtPdf(val(n.altura_2_con_cabeceo_mm), 0),
            apcon: fmtPdf(c.hCon, 1),
            esb: fmtPdf(c.esbeltez, 1),
            factor: fmtPdf(c.factor, 3),
            area: fmtPdf(c.area, 1),
            kn: fmtPdf(val(n.carga_kn), 0),
            kg: fmtPdf(c.kg, 0),
            falla: n.tipo_falla ?? '',
            directo: fmtPdf(c.esfDirecto, 0),
            real: fmtPdf(c.esfReal, 0),
            mpa: fmtPdf(c.esfMpa, 1),
            u: fmtPdf(c.u, 2),
        };
    }

    function construirPayload() {
        const r = recepcionActual;
        const nucleos = r.nucleos || [];
        const fechaInforme = leer('mdFechaInforme');
        const edad = diasEntre(fechaInforme, r.fecha_colado);
        const resistencia = parseFloat(inputResistencia.value);

        // Una hoja (página) por cada 4 núcleos
        const paginas = [];
        for (let i = 0; i < nucleos.length; i += POR_PAGINA) {
            const calcs = calcActual.slice(i, i + POR_PAGINA);
            paginas.push({
                nucleos: nucleos.slice(i, i + POR_PAGINA).map((n, j) => nucleoPdf(n, calcs[j], i + j, r, edad)),
                conformidad: declaracionConformidad(calcs, r.clave_obra, resistencia),
            });
        }
        if (paginas.length === 0) paginas.push({ nucleos: [], conformidad: '' });

        const tn = parseFloat(leer('mdTn'));

        return {
            folio: r.folio,
            n_informe: leer('mdNInforme'),
            fecha_informe: fechaIso(fechaInforme),
            clave_obra: r.clave_obra ?? '',
            cliente: r.cliente ?? '',
            obra: r.obra ?? '',
            procedencia: leer('mdProcedencia'),
            elemento: r.elementos ?? '',
            fecha_extraccion: fechaIso(r.fecha_muest),
            atencion_a: r.atencion_a ?? '',
            muestreado_por: selectMuestreadoPor.value,
            tn: isNaN(tn) ? '' : tn.toFixed(1),
            revenimiento: leer('mdRevenimiento'),
            concreto: leer('mdConcreto'),
            cemento: leer('mdCemento'),
            resistencia: isNaN(resistencia) ? '' : resistencia,
            observaciones: observacionesHoja(selectMuestreadoPor.value),
            paginas: paginas,
        };
    }

    // Antes de enviar a cualquiera de los 2 PDFs se llena el paquete de datos
    form.addEventListener('submit', function () {
        if (!recepcionActual) return;
        document.getElementById('mdPayload').value = JSON.stringify(construirPayload());
    });

    /* ---------- Abrir el modal ---------- */
    document.querySelectorAll('.btn-generar').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const r = JSON.parse(btn.dataset.resultado);

            form.reset(); // fecha de informe en hoy, selects en su valor inicial

            document.getElementById('mdFolio').textContent = r.folio;
            poner('mdRecepcionId', r.id_seg);
            poner('mdInforme', 'LIE. RC. ');
            poner('mdNInforme', r.n_informe);
            poner('mdClave', r.clave_obra);
            poner('mdCliente', r.cliente);
            poner('mdObra', r.obra);
            poner('mdAtencion', r.atencion_a);
            poner('mdElemento', r.elementos);
            poner('mdRecepcion', fecha(r.fecha_rec));
            poner('mdExtraccion', fecha(r.fecha_muest));
            poner('mdDatosProy', r.datos_proy);
            poner('mdProcedencia', r.envia);
            poner('mdRecibe', r.recibe);
            poner('mdDefectos', r.defectos);

            // T.M.N. y resistencia se toman de la primera hoja de la recepción (siguen editables)
            const primero = (r.nucleos || [])[0];
            if (primero) {
                poner('mdTn', primero.tmn);
                poner('mdResistencia', primero.resistencia_proyecto);
            }

            recepcionActual = r;
            calcActual = (r.nucleos || []).map(calcular);

            pintarTabla(r, calcActual);
            actualizarPie();
            document.getElementById('mdAviso').classList.toggle('d-none', r.ensayadas === r.total);
        });
    });
});