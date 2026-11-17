document.addEventListener('DOMContentLoaded', () => {
    const select = document.getElementById('editar-select');
    const campos = document.getElementById('editar-campos');

    select.addEventListener('change', () => {
        const op = select.selectedOptions[0];
        document.getElementById('editar-nombre').value = op.dataset.nombre;
        document.getElementById('editar-paterno').value = op.dataset.paterno;
        document.getElementById('editar-materno').value = op.dataset.materno || '';
        document.getElementById('editar-area').value = op.dataset.area;
        document.getElementById('editar-usuario-input').value = op.dataset.usuario;
        campos.classList.remove('d-none');
    });
});