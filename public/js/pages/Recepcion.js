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
                camposObra[nombre].value = datos[nombre] ?? '';
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

    /* ===================== ESPECÍMENES ===================== */
    const bloques = document.querySelectorAll('.especimen-bloque');
    const numEsp = document.getElementById('num_especimenes');

    function iniciarBloque(bloque) {
        const select = bloque.querySelector('.elemento-select');
        const otro = bloque.querySelector('.elemento-otro');
        const oculto = bloque.querySelector('.elemento-hidden');

        function sincronizar() {
            const esOtro = select.value === '__otro__';
            otro.classList.toggle('d-none', !esOtro);
            otro.required = esOtro && !select.disabled;
            oculto.value = esOtro ? otro.value : select.value;
        }

        select.addEventListener('change', function () {
            sincronizar();
            if (select.value === '__otro__') otro.focus();
        });

        otro.addEventListener('input', function () {
            otro.value = otro.value.toUpperCase();
            oculto.value = otro.value;
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

    /* ===================== FECHAS DE ENSAYO ===================== */
    const inputMuestreo = document.getElementById('fecha_muestreo');
    const checks = document.querySelectorAll('#formRecepcion input[name="fechas_ensayo[]"]');
    const selectEspecimenes = document.getElementById('num_especimenes');
    const limiteFechasMsg = document.getElementById('limite-fechas-msg');

    function formatearFecha(date) {
        const dia = String(date.getDate()).padStart(2, '0');
        const mes = String(date.getMonth() + 1).padStart(2, '0');
        const anio = date.getFullYear();
        return `${dia}/${mes}/${anio}`;
    }

    function actualizarFechasEnsayo() {
        const baseValue = inputMuestreo.value; // formato YYYY-MM-DD
        checks.forEach(function (check) {
            const dias = parseInt(check.dataset.dias, 10);
            const box = document.getElementById('fecha-ensayo-' + dias);

            if (!check.checked || !baseValue) {
                box.textContent = '--';
                box.classList.remove('fecha-aproximada-activa');
                return;
            }

            // Parseamos manualmente para evitar problemas de zona horaria
            const [anio, mes, dia] = baseValue.split('-').map(Number);
            const fechaBase = new Date(anio, mes - 1, dia);
            fechaBase.setDate(fechaBase.getDate() + dias);

            box.textContent = formatearFecha(fechaBase);
            box.classList.add('fecha-aproximada-activa');
        });
    }

    function aplicarLimiteFechas() {
        const limite = parseInt(selectEspecimenes.value, 10) || 0;
        const marcados = Array.from(checks).filter(c => c.checked).length;

        if (limite === 0) {
            limiteFechasMsg.textContent = 'Selecciona el número de especímenes primero.';
        } else {
            limiteFechasMsg.textContent = `Puedes seleccionar hasta ${limite} fecha(s) de ensayo (según tus especímenes).`;
        }

        checks.forEach(function (check) {
            if (!check.checked) {
                check.disabled = limite === 0 || marcados >= limite;
            } else {
                check.disabled = false; // los ya marcados siempre se pueden desmarcar
            }
        });
    }

    checks.forEach(function (check) {
        check.addEventListener('change', actualizarFechasEnsayo);
        check.addEventListener('change', aplicarLimiteFechas);
    });

    inputMuestreo.addEventListener('change', actualizarFechasEnsayo);
    selectEspecimenes.addEventListener('change', function () {
        // Si baja el número de especímenes, desmarca los checks sobrantes
        const limite = parseInt(selectEspecimenes.value, 10) || 0;
        let marcados = 0;
        checks.forEach(function (check) {
            if (check.checked) {
                marcados++;
                if (marcados > limite) check.checked = false;
            }
        });
        actualizarFechasEnsayo();
        aplicarLimiteFechas();
    });

    // Estado inicial (por si el navegador o old() dejó valores)
    actualizarFechasEnsayo();
    aplicarLimiteFechas();

    // Mostrar el paso inicial (el 1, o el que tenga errores del servidor)
    mostrarPaso(pasoActual);
});