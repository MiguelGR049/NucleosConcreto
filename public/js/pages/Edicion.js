document.addEventListener('DOMContentLoaded', function () {

    const PI_XLS = 3.1416;
    const KGF_POR_KN = 101.972;
    const DIV_MPA = 10.1972;
    const DEC_VISTA = 2;   // decimales que se muestran
    const DEC_REAL = 4;    // decimales que se conservan
    const MAX_COLS = 4;

    const modalEl = document.getElementById('modalEdicion');
    const form = document.getElementById('formEdicion');
    const btnGuardar = document.getElementById('btn-guardar-edicion');

    const CAMPOS_NUM = [
        'espesor', 'masa_g', 'diametro_1_mm', 'diametro_2_mm',
        'altura_1_sin_cabeceo_mm', 'altura_2_sin_cabeceo_mm',
        'altura_1_con_cabeceo_mm', 'altura_2_con_cabeceo_mm',
        'carga_kn'
    ];

    const INFO = ['no_nucleo', 'elemento', 'localizacion', 'dia'];

    /* ---------- Redondeo estilo Excel ---------- */
    function redondear(n, dec) {
        const r = Number(Math.round(n + 'e' + dec) + 'e-' + dec);
        return isFinite(r) ? r : n;
    }

    /* ---------- Acceso a las celdas de una columna ---------- */
    function celda(c, k) {
        return modalEl.querySelector('td[data-col="' + c + '"][data-campo="' + k + '"]');
    }

    function ocultoNum(c, k) {
        const td = celda(c, k);
        return td ? td.querySelector('input[type="hidden"]') : null;
    }

    // Lee el valor REAL (el input oculto)
    function valor(c, k) {
        const h = ocultoNum(c, k);
        if (!h || h.value.trim() === '') return null;
        const n = parseFloat(h.value);
        return isNaN(n) ? null : n;
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

    function ponerNumero(c, k, v) {
        const h = ocultoNum(c, k);
        const visible = h.previousElementSibling;
        const n = parseFloat(v);
        h.value = Number.isFinite(n) ? String(n) : '';
        visible.value = '';
        if (h.value !== '') formatearCampo(visible);
    }

    /* ---------- Eventos del formulario ---------- */
    form.addEventListener('focusin', function (e) {
        if (e.target.classList.contains('campo-num')) {
            e.target.value = e.target.nextElementSibling.value;
        }
    });

    form.addEventListener('focusout', function (e) {
        if (e.target.classList.contains('campo-num')) formatearCampo(e.target);
    });

    form.addEventListener('input', function (e) {
        if (e.target.classList.contains('campo-num')) sincronizarNumero(e.target);

        if (e.target.classList.contains('campo-txt') && e.target.classList.contains('text-uppercase')) {
            e.target.value = e.target.value.toUpperCase();
        }

        const td = e.target.closest('td[data-col]');
        if (td) calcular(parseInt(td.dataset.col, 10));
    });

    /* ---------- Fórmulas (mismas que Ensayo.js) ---------- */
    function factorCorreccion(e) {
        if (e === null) return null;
        if (e > 1.75) return 1;
        return (0.2133 * Math.pow(e, 3) - 1.04 * Math.pow(e, 2) + 1.7667 * e) - 0.07;
    }

    function setCalc(c, k, n) {
        const td = celda(c, k);
        const el = td ? td.querySelector('[data-calc]') : null;
        if (!el) return;
        if (n === null || !isFinite(n)) { el.textContent = '—'; return; }
        el.textContent = redondear(n, DEC_VISTA).toLocaleString('es-MX', {
            minimumFractionDigits: DEC_VISTA,
            maximumFractionDigits: DEC_VISTA
        });
    }

    function calcular(c) {
        const masa = valor(c, 'masa_g');
        const d1 = valor(c, 'diametro_1_mm'), d2 = valor(c, 'diametro_2_mm');
        const s1 = valor(c, 'altura_1_sin_cabeceo_mm'), s2 = valor(c, 'altura_2_sin_cabeceo_mm');
        const c1 = valor(c, 'altura_1_con_cabeceo_mm'), c2 = valor(c, 'altura_2_con_cabeceo_mm');
        const kn = valor(c, 'carga_kn');

        const prom = (a, b) => (a !== null && b !== null) ? (a + b) / 2 : null;

        const dProm = prom(d1, d2);
        const hSin = prom(s1, s2);
        const hCon = prom(c1, c2);

        const area = dProm ? (dProm * dProm * PI_XLS / 4) / 100 : null;

        const masaUnit = (masa && hSin && area)
            ? (masa / (hSin * area)) * 10000
            : null;

        const esbeltez = (hCon !== null && dProm) ? hCon / dProm : null;
        const factor = factorCorreccion(esbeltez);

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

    /* ---------- Llenar el modal con una hoja ---------- */
    let old = {};
    try { old = JSON.parse(modalEl.dataset.old || '{}') || {}; } catch (e) { old = {}; }

    function fechaCorta(f) {
        const m = String(f || '').match(/^(\d{4})-(\d{2})-(\d{2})/);
        return m ? m[3] + '/' + m[2] + '/' + m[1] : (f || '');
    }

    function llenarModal(hoja) {
        btnGuardar.disabled = false;

        document.getElementById('e-hoja-id').value = hoja.hoja_id;
        document.getElementById('e-folio-titulo').textContent = hoja.folio || '';
        document.getElementById('e-fecha').value = fechaCorta(hoja.fecha_ensayo);
        document.getElementById('e-clave-obra').value = hoja.clave_obra || '';
        document.getElementById('e-obra').value = hoja.obra || '';

        // Si el servidor devolvió errores de esta hoja, conservar lo que se escribió
        const reabriendo = String(old.hoja_id || '') === String(hoja.hoja_id);

        // Limpiar todas las columnas
        modalEl.querySelectorAll('td[data-col]').forEach(function (td) {
            td.classList.add('d-none');
            td.querySelectorAll('input:not([type="hidden"]), select').forEach(function (el) {
                el.removeAttribute('name');
                el.disabled = true;
            });
            td.querySelectorAll('input[type="hidden"]').forEach(function (el) {
                el.removeAttribute('name');
                el.value = '';
                el.disabled = true;
            });
        });

        (hoja.nucleos || []).slice(0, MAX_COLS).forEach(function (n, c) {
            const prefijo = 'nucleos[' + n.id_seg + ']';
            const fuente = (reabriendo && old.nucleos && old.nucleos[n.id_seg]) ? old.nucleos[n.id_seg] : n;

            // Mostrar la columna
            modalEl.querySelectorAll('td[data-col="' + c + '"]').forEach(function (td) {
                td.classList.remove('d-none');
            });

            // Solo lectura
            INFO.forEach(function (k) {
                const td = celda(c, k);
                const el = td.querySelector('[data-info]');
                const v = k === 'dia' ? hoja.dia : n[k];
                el.textContent = (v !== null && v !== undefined && v !== '') ? v : '—';
            });

            // Números (visible + oculto con el valor real)
            CAMPOS_NUM.forEach(function (k) {
                const td = celda(c, k);
                const visible = td.querySelector('.campo-num');
                const oculto = td.querySelector('input[type="hidden"]');
                visible.disabled = false;
                oculto.disabled = false;
                oculto.name = prefijo + '[' + k + ']';
                ponerNumero(c, k, fuente[k]);
            });

            // Tipo de falla
            const sel = celda(c, 'tipo_falla').querySelector('.campo-sel');
            sel.disabled = false;
            sel.name = prefijo + '[tipo_falla]';
            sel.value = fuente.tipo_falla ?? '';

            calcular(c);
        });

        modalEl.querySelector('.modal-body').scrollTop = 0;
    }

    modalEl.addEventListener('show.bs.modal', function (event) {
        const boton = event.relatedTarget;
        if (!boton) return;

        let hoja;
        try { hoja = JSON.parse(boton.dataset.hoja); } catch (e) { return; }
        llenarModal(hoja);
    });

    /* ---------- Envío ---------- */
    form.addEventListener('submit', function (e) {

        // Botón "Eliminar registro": no valida campos, solo pide confirmación
        if (e.submitter && e.submitter.id === 'btn-eliminar-edicion') {
            const folio = document.getElementById('e-folio-titulo').textContent;
            const ok = confirm(
                '¿Eliminar TODO el registro (folio ' + folio + ')?\n' +
                'Se ocultará y se borrará definitivamente en 30 días.'
            );
            if (!ok) e.preventDefault();
            return;
        }

        // Campos obligatorios de las columnas visibles
        const vacio = Array.from(modalEl.querySelectorAll('td[data-col]:not(.d-none)[data-req="1"]'))
            .map(function (td) {
                const num = td.querySelector('.campo-num');
                if (num) return num.nextElementSibling.value === '' ? num : null;
                const txt = td.querySelector('.campo-txt');
                return (txt && txt.value.trim() === '') ? txt : null;
            })
            .find(Boolean);

        if (vacio) {
            e.preventDefault();
            vacio.setCustomValidity('Este campo es obligatorio');
            vacio.reportValidity();
            vacio.setCustomValidity('');
            return;
        }

        btnGuardar.disabled = true;   // evita doble envío
    });

    // Reabrir el modal si el servidor devolvió errores
    if (old.hoja_id && modalEl.dataset.hayErrores === '1') {
        const boton = Array.from(document.querySelectorAll('.btn-editar')).find(function (b) {
            try { return String(JSON.parse(b.dataset.hoja).hoja_id) === String(old.hoja_id); }
            catch (e) { return false; }
        });
        if (boton) bootstrap.Modal.getOrCreateInstance(modalEl).show(boton);
    }
});