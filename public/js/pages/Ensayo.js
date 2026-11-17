document.addEventListener('DOMContentLoaded', function () {

    const PI_XLS = 3.1416;
    const KGF_POR_KN = 101.972;
    const DIV_MPA = 10.1972;
    const DEC_VISTA = 2;   // decimales que se muestran
    const DEC_REAL = 4;    // decimales que se conservan

    const modalEl = document.getElementById('modalEnsayo');
    const form = document.getElementById('formEnsayo');
    const btnGuardar = document.getElementById('btn-guardar-ensayo');

    // campo del formulario -> nombre del data-* del botón
    const CAMPOS_NUM = [
        'masa_g', 'diametro_1_mm', 'diametro_2_mm',
        'altura_1_sin_cabeceo_mm', 'altura_2_sin_cabeceo_mm',
        'altura_1_con_cabeceo_mm', 'altura_2_con_cabeceo_mm',
        'carga_kn'
    ];
    const INFO = ['elemento', 'espesor', 'localizacion', 'dia'];

    /* ---------- Redondeo estilo Excel ---------- */
    function redondear(n, dec) {
        const r = Number(Math.round(n + 'e' + dec) + 'e-' + dec);
        return isFinite(r) ? r : n;
    }

    /* ---------- Campos numéricos: vista a 2 decimales, valor real oculto ---------- */
    function sincronizarNumero(t) {
        let txt = t.value;

        txt = txt.includes('.') ? txt.replace(/,/g, '') : txt.replace(',', '.');
        txt = txt.replace(/[^0-9.]/g, '');

        const punto = txt.indexOf('.');
        if (punto !== -1) {
            const entero = txt.slice(0, punto);
            const decimales = txt.slice(punto + 1).replace(/\./g, '').slice(0, DEC_REAL);
            txt = entero + '.' + decimales;
        }

        if (txt !== t.value) t.value = txt;

        t.nextElementSibling.value = txt.replace(/\.$/, '').replace(/^\./, '0.');
    }

    function formatearCampo(t) {
        const oculto = t.nextElementSibling;
        if (oculto.value === '') { t.value = ''; return; }
        t.value = redondear(parseFloat(oculto.value), DEC_VISTA).toFixed(DEC_VISTA);
    }

    form.addEventListener('focusin', function (e) {
        if (e.target.classList.contains('campo-num')) {
            e.target.value = e.target.nextElementSibling.value;
        }
    });

    form.addEventListener('focusout', function (e) {
        if (e.target.classList.contains('campo-num')) formatearCampo(e.target);
    });

    form.addEventListener('input', function (e) {
        if (e.target.classList.contains('campo-num')) {
            sincronizarNumero(e.target);
        }
        calcular();
    });

    /* ---------- Helpers ---------- */
    function campo(k) {
        return form.querySelector('[name="' + k + '"]');
    }

    // Lee el valor REAL (el input oculto lleva el name)
    function valor(k) {
        const el = campo(k);
        if (!el || el.value.trim() === '') return null;
        const n = parseFloat(el.value);
        return isNaN(n) ? null : n;
    }

    // Pone un valor en un campo numérico (visible + oculto)
    function ponerNumero(k, v) {
        const oculto = campo(k);
        const visible = oculto.previousElementSibling;
        const n = parseFloat(v);
        oculto.value = Number.isFinite(n) ? String(n) : '';
        visible.value = '';
        if (oculto.value !== '') formatearCampo(visible);
    }

    function setInfo(k, texto) {
        const el = modalEl.querySelector('[data-info="' + k + '"]');
        if (el) el.textContent = (texto !== undefined && texto !== null && texto !== '') ? texto : '—';
    }

    function setCalc(k, n) {
        const el = modalEl.querySelector('[data-calc="' + k + '"]');
        if (!el) return;
        if (n === null || !isFinite(n)) { el.textContent = '—'; return; }
        el.textContent = redondear(n, DEC_VISTA).toLocaleString('es-MX', {
            minimumFractionDigits: DEC_VISTA,
            maximumFractionDigits: DEC_VISTA
        });
    }

    // Igual que la fila 22 del Excel
    function factorCorreccion(e) {
        if (e === null) return null;
        if (e > 1.75) return 1;
        return (0.2133 * Math.pow(e, 3) - 1.04 * Math.pow(e, 2) + 1.7667 * e) - 0.07;
    }

    /* ---------- Fórmulas ---------- */
    function calcular() {
        const masa = valor('masa_g');
        const d1 = valor('diametro_1_mm'), d2 = valor('diametro_2_mm');
        const s1 = valor('altura_1_sin_cabeceo_mm'), s2 = valor('altura_2_sin_cabeceo_mm');
        const c1 = valor('altura_1_con_cabeceo_mm'), c2 = valor('altura_2_con_cabeceo_mm');
        const kn = valor('carga_kn');

        const prom = (a, b) => (a && b !== null) ? (a + b) / 2 : null;

        const dProm = prom(d1, d2);
        const hSin = prom(s1, s2);
        const hCon = prom(c1, c2);

        const area = (dProm !== null) ? (dProm * dProm * PI_XLS / 4) / 100 : null;

        const masaUnit = (masa && hSin && area)
            ? (masa / (hSin * area)) * 10000
            : null;

        const esbeltez = (hCon !== null && dProm) ? hCon / dProm : null;
        const factor = factorCorreccion(esbeltez);

        const kg = kn ? kn * KGF_POR_KN : null;
        const esfDirecto = (kg !== null && area) ? kg / area : null;
        const esfReal = (esfDirecto !== null && factor !== null) ? esfDirecto * factor : null;
        const esfMpa = esfReal !== null ? esfReal / DIV_MPA : null;

        setCalc('masa_unitaria', masaUnit);
        setCalc('diametro_prom', dProm);
        setCalc('altura_prom_sin', hSin);
        setCalc('altura_prom_con', hCon);
        setCalc('esbeltez', esbeltez);
        setCalc('factor', factor);
        setCalc('area', area);
        setCalc('carga_kg', kg);
        setCalc('esf_directo', esfDirecto);
        setCalc('esf_real', esfReal);
        setCalc('esf_real_mpa', esfMpa);
    }

    /* ---------- Repartir renglones: por capturar / ya capturado ---------- */
    const filasTabla = Array.from(modalEl.querySelectorAll('tr[data-fila]'));
    const tbPendiente = document.getElementById('tbody-pendiente');
    const tbCapturado = document.getElementById('tbody-capturado');

    function estaVacio(d, k) {
        const v = d[k];
        if (k === 'tipo_falla') return !['I', 'II', 'III', 'IV', 'V', 'VI'].includes(String(v ?? '').trim());
        return !Number.isFinite(parseFloat(v));
    }

    // Se decide una sola vez al abrir, con lo que hay en la base de datos
    function repartirFilas(d) {
        filasTabla.forEach(function (tr) {
            const deps = tr.dataset.dep ? tr.dataset.dep.split(',') : [];
            const pendiente = deps.some(function (k) { return estaVacio(d, k); });
            (pendiente ? tbPendiente : tbCapturado).appendChild(tr);
        });

        // Si no falta nada, se oculta el bloque "Por capturar"
        tbPendiente.hidden = !tbPendiente.querySelector('tr[data-fila]');
    }

    /* ---------- Abrir el modal ---------- */
    let old = {};
    try { old = JSON.parse(modalEl.dataset.old || '{}') || {}; } catch (e) { old = {}; }

    modalEl.addEventListener('show.bs.modal', function (event) {
        const boton = event.relatedTarget;
        if (!boton) return;

        const d = boton.dataset;
        btnGuardar.disabled = false;

        document.getElementById('ensayoNucleoId').value = d.id;
        document.getElementById('ensayoFolio').textContent = d.folio;
        document.getElementById('ensayoNumero').textContent = d.nucleo;
        document.getElementById('h-fecha').value = d.fecha || '';
        document.getElementById('h-clave-obra').value = d.claveObra || '';
        document.getElementById('h-obra').value = d.obra || '';

        INFO.forEach(function (k) { setInfo(k, d[k]); });

        // Si hubo error de validación en este núcleo, conservar lo que se escribió
        const reabriendo = String(old.nucleo_id || '') === String(d.id);
        const fuente = reabriendo ? old : d;

        CAMPOS_NUM.forEach(function (k) { ponerNumero(k, fuente[k]); });
        campo('tipo_falla').value = fuente.tipo_falla ?? '';

        repartirFilas(d);
        modalEl.querySelector('.modal-body').scrollTop = 0;

        calcular();
    });

    /* ---------- Envío ---------- */
    form.addEventListener('submit', function (e) {
        const invalido = Array.from(form.querySelectorAll('.campo-num')).find(function (t) {
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

    // Reabrir el modal automáticamente si el servidor devolvió errores
    if (old.nucleo_id && modalEl.dataset.hayErrores === '1') {
        const boton = document.querySelector('.btn-ensayar[data-id="' + old.nucleo_id + '"]');
        if (boton) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show(boton);
        }
    }
});