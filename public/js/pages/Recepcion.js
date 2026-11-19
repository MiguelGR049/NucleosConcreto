document.addEventListener('DOMContentLoaded', function () {

    /* ===================== PASOS ===================== */
    const form = document.getElementById('formRecepcion');
    const pasos = document.querySelectorAll('.paso-form');
    const itemsPaso = document.querySelectorAll('[data-ir-paso]');
    const pagAnterior = document.getElementById('pagAnterior');
    const pagSiguiente = document.getElementById('pagSiguiente');
    const contenedorGuardar = document.getElementById('contenedorGuardar');
    const totalPasos = pasos.length;
    let pasoActual = parseInt(form.dataset.pasoInicial, 10) || 1;

    function mostrarPaso(n) {
        pasoActual = n;

        pasos.forEach(function (p) {
            p.classList.toggle('d-none', parseInt(p.dataset.paso, 10) !== n);
        });

        itemsPaso.forEach(function (item) {
            const activo = parseInt(item.dataset.irPaso, 10) === n;
            item.classList.toggle('active', activo);
            const link = item.querySelector('.page-link');
            if (activo) {
                link.setAttribute('aria-current', 'page');
            } else {
                link.removeAttribute('aria-current');
            }
        });

        pagAnterior.classList.toggle('disabled', n === 1);
        pagSiguiente.classList.toggle('disabled', n === totalPasos);
        contenedorGuardar.classList.toggle('d-none', n !== totalPasos);

        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    function pasoValido(n) {
        const paso = document.querySelector('.paso-form[data-paso="' + n + '"]');
        const campos = paso.querySelectorAll('input, select, textarea');
        for (const campo of campos) {
            if (!campo.checkValidity()) {
                // Si el campo inválido está en otro paso, primero lo mostramos
                if (pasoActual !== n) mostrarPaso(n);
                campo.reportValidity();
                return false;
            }
        }
        return true;
    }

    // Ir a un paso: hacia atrás siempre libre; hacia adelante valida los pasos intermedios
    function irAPaso(destino) {
        if (destino < 1 || destino > totalPasos || destino === pasoActual) return;

        if (destino > pasoActual) {
            for (let n = pasoActual; n < destino; n++) {
                if (!pasoValido(n)) return;
            }
        }
        mostrarPaso(destino);
    }

    itemsPaso.forEach(function (item) {
        item.querySelector('.page-link').addEventListener('click', function () {
            irAPaso(parseInt(item.dataset.irPaso, 10));
        });
    });

    pagAnterior.querySelector('.page-link').addEventListener('click', function () {
        irAPaso(pasoActual - 1);
    });

    pagSiguiente.querySelector('.page-link').addEventListener('click', function () {
        irAPaso(pasoActual + 1);
    });

    // Evita que Enter envíe el formulario antes del último paso
    form.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA' && pasoActual < totalPasos) {
            e.preventDefault();
            irAPaso(pasoActual + 1);
        }
    });

    /* ===================== MAYÚSCULAS EN CAMPOS DE TEXTO ===================== */
    const selectorTexto = 'input[type="text"], textarea';

    // Convierte mientras se escribe, conservando la posición del cursor
    form.addEventListener('input', function (e) {
        const el = e.target;
        if (!el.matches(selectorTexto)) return;

        const inicio = el.selectionStart;
        const fin = el.selectionEnd;
        el.value = el.value.toUpperCase();
        el.setSelectionRange(inicio, fin);
    });

    // Por si algún valor llega por old() o por autorrelleno
    function convertirTodoAMayusculas() {
        form.querySelectorAll(selectorTexto).forEach(function (el) {
            if (!el.readOnly) el.value = el.value.toUpperCase();
        });
    }
    convertirTodoAMayusculas();
    form.addEventListener('submit', convertirTodoAMayusculas);

    /* ===================== AUTORRELLENO POR CLAVE DE OBRA ===================== */
    const inputClave = document.getElementById('clave_obra');
    const urlObra = form.dataset.urlObra;
    const camposObra = {
        cliente: document.getElementById('cliente'),
        obra: document.getElementById('obra'),
        atencion_a: document.getElementById('atencion_a'),
    };

    let temporizadorObra;
    let ultimaClave = '';

    async function buscarObra() {
        const valor = inputClave.value.trim();
        if (valor === '' || valor === ultimaClave) return;
        ultimaClave = valor;

        try {
            const resp = await fetch(urlObra + '?clave=' + encodeURIComponent(valor), {
                headers: { 'Accept': 'application/json' }
            });
            if (!resp.ok) return;

            const datos = await resp.json();
            if (!datos.encontrada) return;

            for (const nombre in camposObra) {
                camposObra[nombre].value = (datos[nombre] ?? '').toUpperCase();
            }
        } catch (e) {
            console.error('No se pudo consultar la obra', e);
        }
    }

    if (inputClave && urlObra) {
        // Busca 400 ms después de dejar de escribir, y también al salir del campo
        inputClave.addEventListener('input', function () {
            clearTimeout(temporizadorObra);
            temporizadorObra = setTimeout(buscarObra, 400);
        });
        inputClave.addEventListener('blur', buscarObra);
    }

    /* ===================== VERIFICAR NÚMERO DE INFORME ===================== */
    const inputInforme = document.getElementById('numero_informe');
    const urlInforme = form.dataset.urlInforme;
    const MSG_INFORME = 'Este número de informe ya existe.';

    let temporizadorInforme;
    let ultimoInforme = '';

    function limpiarInforme() {
        inputInforme.setCustomValidity('');
        inputInforme.classList.remove('is-invalid');
    }

    async function verificarInforme() {
        const valor = inputInforme.value.trim().toUpperCase();

        if (valor === '') {
            ultimoInforme = '';
            limpiarInforme();
            return;
        }
        if (valor === ultimoInforme) return;
        ultimoInforme = valor;

        try {
            const resp = await fetch(urlInforme + '?numero=' + encodeURIComponent(valor), {
                headers: { 'Accept': 'application/json' }
            });
            if (!resp.ok) return;

            const datos = await resp.json();

            // Si el usuario siguió escribiendo mientras respondía el servidor, se ignora
            if (inputInforme.value.trim().toUpperCase() !== valor) return;

            if (datos.existe) {
                inputInforme.setCustomValidity(MSG_INFORME);
                inputInforme.classList.add('is-invalid');
            } else {
                limpiarInforme();
            }
        } catch (e) {
            console.error('No se pudo verificar el número de informe', e);
        }
    }

    if (inputInforme && urlInforme) {
        inputInforme.addEventListener('input', function () {
            limpiarInforme();
            ultimoInforme = '';
            clearTimeout(temporizadorInforme);
            temporizadorInforme = setTimeout(verificarInforme, 400);
        });
        inputInforme.addEventListener('blur', verificarInforme);

        // Si la respuesta llega cuando el usuario ya está en otro paso, el navegador
        // no puede enfocar un campo oculto y bloquearía el envío sin avisar
        form.addEventListener('submit', function (e) {
            if (!inputInforme.checkValidity()) {
                e.preventDefault();
                mostrarPaso(1);
                inputInforme.reportValidity();
            }
        });
    }

    /* ===================== ESPECÍMENES ===================== */
    const bloques = document.querySelectorAll('.especimen-bloque');
    const numEsp = document.getElementById('num_especimenes');

    function iniciarBloque(bloque) {
        const select = bloque.querySelector('.elemento-select');
        const otro = bloque.querySelector('.elemento-otro');
        const oculto = bloque.querySelector('.elemento-hidden');
        const fechaColado = bloque.querySelector('.fecha-colado');
        const sinColado = bloque.querySelector('.sin-colado');
        let coladoPrevio = fechaColado.value;

        function sincronizarColado() {
            if (sinColado.checked) {
                if (fechaColado.value) coladoPrevio = fechaColado.value;
                fechaColado.value = '';
                fechaColado.disabled = true;
            } else if (!bloque.classList.contains('d-none')) {
                fechaColado.disabled = false;
            }
        }

        function sincronizar() {
            const esOtro = select.value === '__otro__';
            otro.addEventListener('input', sincronizar);
            otro.classList.toggle('d-none', !esOtro);
            otro.required = esOtro && !select.disabled;
            oculto.value = esOtro ? otro.value : select.value;
            sincronizarColado();
        }

        sinColado.addEventListener('change', function () {
            sincronizarColado();
            if (!sinColado.checked && !fechaColado.value) fechaColado.value = coladoPrevio;
        });

        select.addEventListener('change', function () {
            sincronizar();
            if (select.value === '__otro__') otro.focus();
        });

        // Restaurar si el servidor devolvió el formulario con errores
        const previo = oculto.value;
        if (previo) {
            const existe = Array.from(select.options).some(op => op.value === previo && op.value !== '__otro__');
            if (existe) {
                select.value = previo;
            } else {
                select.value = '__otro__';
                otro.value = previo;
            }
        }
        sincronizar();
        bloque._sincronizar = sincronizar;
    }

    function mostrarBloques() {
        const n = parseInt(numEsp.value, 10) || 0;
        bloques.forEach(function (bloque, i) {
            const visible = i < n;
            bloque.classList.toggle('d-none', !visible);
            // Los campos deshabilitados no se envían ni se validan
            bloque.querySelectorAll('input, select').forEach(c => c.disabled = !visible);
            bloque._sincronizar();
        });
    }

    bloques.forEach(iniciarBloque);
    numEsp.addEventListener('change', mostrarBloques);
    mostrarBloques();

    /* ===================== FECHA DE ENSAYO (7 DÍAS) ===================== */
    const DIAS_ENSAYO = 7;
    const inputMuestreo = document.getElementById('fecha_muestreo');
    const boxEnsayo = document.getElementById('fecha-ensayo-' + DIAS_ENSAYO);

    function actualizarFechaEnsayo() {
        if (!inputMuestreo.value) {
            boxEnsayo.textContent = '--';
            boxEnsayo.classList.remove('fecha-aproximada-activa');
            return;
        }
        const [anio, mes, dia] = inputMuestreo.value.split('-').map(Number);
        const fecha = new Date(anio, mes - 1, dia);
        fecha.setDate(fecha.getDate() + DIAS_ENSAYO);

        const dd = String(fecha.getDate()).padStart(2, '0');
        const mm = String(fecha.getMonth() + 1).padStart(2, '0');
        boxEnsayo.textContent = `${dd}/${mm}/${fecha.getFullYear()}`;
        boxEnsayo.classList.add('fecha-aproximada-activa');
    }

    inputMuestreo.addEventListener('change', actualizarFechaEnsayo);
    actualizarFechaEnsayo();

    // Mostrar el paso inicial (el 1, o el que tenga errores del servidor)
    mostrarPaso(pasoActual);
});