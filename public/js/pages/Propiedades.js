document.addEventListener('DOMContentLoaded', function () {

    const aviso = document.getElementById('aviso-captura');
    const modalEl = document.getElementById('modalHoja');
    const formHoja = document.getElementById('formHoja');
    const btnGuardar = document.getElementById('btn-guardar-hoja');
    const btnAgregar = document.getElementById('btn-agregar-nucleo');
    const btnQuitar = document.getElementById('btn-quitar-nucleo');
    const contador = document.getElementById('contador-nucleos');
    const infoCap = document.getElementById('info-capacidad');

    const MAX_COLUMNAS = 4;          // columnas que tiene la hoja
    const DEC_VISTA = 2;          // decimales que se muestran
    const DEC_REAL = 4;          // decimales que se conservan (igual que la base de datos)
    const PI_XLS = 3.1416;     // el mismo valor que usa el Excel
    const KGF_POR_KN = 101.972;    // kN -> kg
    const DIV_MPA = 10.1972;    // kg/cm² -> MPa

    let maxPermitido = 1;            // tope para la fecha elegida (lo calcula el servidor)
    let nucleosPend = [];            // núcleos pendientes de la recepción elegida
    let nColumnas = 1;            // columnas visibles ahora

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
            const entero = txt.slice(0, punto);
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
        document.getElementById('h-obra').value = d.obra || '';
        document.getElementById('h-extraccion').value = d.extraccion || '';
        document.getElementById('h-datos').value = d.datos || '';

        maxPermitido = Math.min(parseInt(d.max, 10), MAX_COLUMNAS);

        infoCap.textContent =
            'Quedan ' + d.restantes + ' núcleo(s) por ensayar en esta recepción y ' +
            d.pendientes + ' fecha(s) de ensayo pendiente(s). ' +
            'En esta fecha puedes capturar hasta ' + maxPermitido + '.';

        nucleosPend = JSON.parse(d.nucleos || '[]');
        llenarSelects();

        // La edad es la misma en todas las columnas
        for (let c = 0; c < MAX_COLUMNAS; c++) {
            setInfo(c, 'dia', d.dia);
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

                td.querySelectorAll('input, select').forEach(function (i) {
                    i.disabled = !activa;
                    i.required = activa;
                    if (!activa) i.value = '';
                });
            });
        }

        // Preselecciona el primer núcleo libre en las columnas activas sin elegir
        for (let c = 0; c < n; c++) {
            const sel = selectNucleo(c);
            if (!sel.value) {
                const libre = nucleosPend.find(x => !idsUsados().has(String(x.id_seg)));
                if (libre) sel.value = String(libre.id_seg);
            }
        }

        for (let c = 0; c < MAX_COLUMNAS; c++) {
            mostrarInfoNucleo(c);
            calcular(c);
        }
        refrescarOpciones();

        contador.textContent = 'Núcleos: ' + n + ' de ' + maxPermitido;
        btnAgregar.disabled = n >= maxPermitido;
        btnQuitar.disabled = n <= 1;
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
    function selectNucleo(c) {
        return document.querySelector('[name="nucleos[' + c + '][id_nucleo]"]');
    }

    function llenarSelects() {
        document.querySelectorAll('.select-nucleo').forEach(function (sel) {
            sel.innerHTML = '<option value="">Seleccione...</option>';
            nucleosPend.forEach(function (n) {
                const op = document.createElement('option');
                op.value = n.id_seg;
                op.textContent = n.no_nucleo;
                sel.appendChild(op);
            });
        });
    }

    function idsUsados() {
        const usados = new Set();
        for (let c = 0; c < nColumnas; c++) {
            const v = selectNucleo(c).value;
            if (v) usados.add(v);
        }
        return usados;
    }

    // Un núcleo elegido en una columna no se puede elegir en otra
    function refrescarOpciones() {
        const usados = idsUsados();
        for (let c = 0; c < MAX_COLUMNAS; c++) {
            const sel = selectNucleo(c);
            Array.from(sel.options).forEach(function (op) {
                op.disabled = op.value !== '' && usados.has(op.value) && op.value !== sel.value;
            });
        }
    }

    function mostrarInfoNucleo(c) {
        const sel = selectNucleo(c);
        const n = nucleosPend.find(x => String(x.id_seg) === sel.value);
        setInfo(c, 'elemento', n ? n.elemento : '');
        setInfo(c, 'espesor', n ? n.espesor : '');
        setInfo(c, 'localizacion', n ? n.localizacion : '');
    }

    formHoja.addEventListener('change', function (e) {
        if (!e.target.classList.contains('select-nucleo')) return;
        const td = e.target.closest('[data-col]');
        mostrarInfoNucleo(parseInt(td.dataset.col, 10));
        refrescarOpciones();
    });

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
        const d1 = valor(c, 'diametro_1_mm'), d2 = valor(c, 'diametro_2_mm');
        const s1 = valor(c, 'altura_1_sin_cabeceo_mm'), s2 = valor(c, 'altura_2_sin_cabeceo_mm');
        const c1 = valor(c, 'altura_1_con_cabeceo_mm'), c2 = valor(c, 'altura_2_con_cabeceo_mm');
        const kn = valor(c, 'carga_kn');

        // En el Excel, una celda vacía o en 0 deja el resultado en blanco.
        const prom = (a, b) => (a && b !== null) ? (a + b) / 2 : null;

        const dProm = prom(d1, d2);    // fila 14
        const hSin = prom(s1, s2);    // fila 17
        const hCon = prom(c1, c2);    // fila 20

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
        const kg = kn ? kn * KGF_POR_KN : null;
        const esfDirecto = (kg !== null && area) ? kg / area : null;
        const esfReal = (esfDirecto !== null && factor !== null) ? esfDirecto * factor : null;
        const esfMpa = esfReal !== null ? esfReal / DIV_MPA : null;

        setCalc(c, 'masa_unitaria', masaUnit);
        setCalc(c, 'diametro_prom', dProm);
        setCalc(c, 'altura_prom_sin', hSin);
        setCalc(c, 'altura_prom_con', hCon);
        setCalc(c, 'esbeltez', esbeltez);
        setCalc(c, 'factor', factor);
        setCalc(c, 'area', area);
        setCalc(c, 'carga_kg', kg);
        setCalc(c, 'esf_directo', esfDirecto);
        setCalc(c, 'esf_real', esfReal);
        setCalc(c, 'esf_real_mpa', esfMpa);
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