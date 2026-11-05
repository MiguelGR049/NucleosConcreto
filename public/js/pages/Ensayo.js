document.addEventListener('DOMContentLoaded', function () {

    const PI_XLS = 3.1416;
    const KGF_POR_KN = 101.972;
    const DIV_MPA = 10.1972;

    const modalEl = document.getElementById('modalEnsayo');
    const cargaInput = document.getElementById('ensayoCarga');
    const fallaInput = document.getElementById('ensayoFalla');
    let medidas = {};

    function numero(valor) {
        const n = parseFloat(valor);
        return Number.isFinite(n) ? n : null;
    }

    function promedio(a, b) {
        return a !== null && b !== null && a > 0 && b > 0 ? (a + b) / 2 : null;
    }

    function factorCorreccion(e) {
        if (e === null) return null;
        if (e > 1.75) return 1;
        return (0.2133 * Math.pow(e, 3) - 1.04 * Math.pow(e, 2) + 1.7667 * e) - 0.07;
    }

    // Solo para mostrar: redondea a 2 decimales sin alterar el valor original
    function aDosDecimales(valor) {
        const n = numero(valor);
        return n === null ? '' : n.toFixed(2);
    }

    // Los resultados se muestran a 2 decimales; el cálculo usa siempre los completos
    function mostrar(campo, valor) {
        const input = modalEl.querySelector('[data-resultado="' + campo + '"]');
        if (!input) return;
        input.value = valor === null || !Number.isFinite(valor) ? '' : valor.toFixed(2);
    }

    function calcular() {
        const kn = numero(cargaInput.value);

        // medidas contiene los valores completos tal como vienen de la base de datos
        const dProm = promedio(numero(medidas.d1), numero(medidas.d2));
        const hCon = promedio(numero(medidas.c1), numero(medidas.c2));

        const area = dProm !== null ? (dProm * dProm * PI_XLS / 4) / 100 : null;
        const esbeltez = hCon !== null && dProm !== null ? hCon / dProm : null;
        const factor = factorCorreccion(esbeltez);

        const kg = kn !== null && kn > 0 ? kn * KGF_POR_KN : null;
        const directo = kg !== null && area !== null && area > 0 ? kg / area : null;
        const real = directo !== null && factor !== null ? directo * factor : null;
        const mpa = real !== null ? real / DIV_MPA : null;

        mostrar('area', area);
        mostrar('kg', kg);
        mostrar('directo', directo);
        mostrar('real', real);
        mostrar('mpa', mpa);
    }

    // Llenar el modal con los datos del núcleo que se pulsó
    modalEl.addEventListener('show.bs.modal', function (event) {
        const boton = event.relatedTarget;
        if (!boton) return;

        const d = boton.dataset;
        medidas = d;

        document.getElementById('ensayoNucleoId').value = d.id;
        document.getElementById('ensayoFolio').textContent = d.folio;
        document.getElementById('ensayoNumero').textContent = d.nucleo;

        // La tabla muestra 2 decimales; "medidas" conserva los valores completos
        ['masa', 'd1', 'd2', 's1', 's2', 'c1', 'c2'].forEach(function (k) {
            modalEl.querySelector('[data-medida="' + k + '"]').textContent = aDosDecimales(d[k]);
        });

        // Si hubo error de validación en este núcleo, conservar lo que se escribió
        const reabriendo = modalEl.dataset.oldNucleo === d.id;
        cargaInput.value = reabriendo ? modalEl.dataset.oldCarga : (d.carga ?? '');
        fallaInput.value = reabriendo ? modalEl.dataset.oldFalla : (d.falla ?? '');

        calcular();
    });

    cargaInput.addEventListener('input', calcular);

    // Reabrir el modal automáticamente si el servidor devolvió errores
    // Reabrir el modal automáticamente si el servidor devolvió errores
    const oldId = modalEl.dataset.oldNucleo;
    if (oldId && modalEl.dataset.hayErrores === '1') {
        const boton = document.querySelector('.btn-ensayar[data-id="' + oldId + '"]');
        if (boton) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show(boton);
        }
    }
});