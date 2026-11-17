document.addEventListener('DOMContentLoaded', () => {
    const select = document.getElementById('editar-select');
    const campos = document.getElementById('editar-campos');

    if (select) {
        select.addEventListener('change', () => {
            const op = select.selectedOptions[0];
            document.getElementById('editar-nombre').value = op.dataset.nombre;
            document.getElementById('editar-paterno').value = op.dataset.paterno;
            document.getElementById('editar-materno').value = op.dataset.materno || '';
            document.getElementById('editar-area').value = op.dataset.area;
            document.getElementById('editar-usuario-input').value = op.dataset.usuario;
            campos.classList.remove('d-none');
        });
    }
});

/**
 * Función para mostrar u ocultar la contraseña con soporte para Font Awesome SVG
 * @param {HTMLElement} btn - El botón que disparó el evento
 */
window.mostrarPassword = function (btn) {
    const input = btn.closest('.input-group').querySelector('input');
    const svg = btn.querySelector('svg'); // Buscamos el SVG que genera Font Awesome

    if (input.type === 'password') {
        input.type = 'text';
        if (svg) {
            svg.classList.remove('fa-eye-slash');
            svg.classList.add('fa-eye');
            svg.setAttribute('data-icon', 'eye'); // Actualizamos el icono interno de Font Awesome
        }
    } else {
        input.type = 'password';
        if (svg) {
            svg.classList.remove('fa-eye');
            svg.classList.add('fa-eye-slash');
            svg.setAttribute('data-icon', 'eye-slash'); // Volvemos al icono tachado
        }
    }
}