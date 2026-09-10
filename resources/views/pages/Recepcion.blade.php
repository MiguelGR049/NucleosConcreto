@extends('plantilla')

@section('contenido')

@section('navbar-title', 'Registro de probeta')
@section('back-route', route('inicio'))

@include('layouts.navbar')
<div class="container mt-4 mb-5">
    <div class="card recepcion-card">
        <div class="card-body p-4">

            <form action="#" method="POST" id="formRecepcion">
                @csrf

                <div class="row g-3">

                    <div class="col-12">
                        <label class="form-label">Folio</label>
                        <input type="text" class="form-control" value="{{ $folioGenerado }}" readonly>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Clave de obra</label>
                        <input type="text" name="localizacion" class="form-control" placeholder="Ej. OB-001">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Cliente</label>
                        <input type="text" name="localizacion" class="form-control" placeholder="Ej. Obra XYZ S.A. de C.V.">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Obra</label>
                        <input type="text" name="localizacion" class="form-control" placeholder="Ej. Edificio ABC">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Antecion a:</label>
                        <input type="text" name="localizacion" class="form-control" placeholder="Ej. Ing. Juan Pérez">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Número de muestra</label>
                        <input type="text" name="localizacion" class="form-control" placeholder="Ej. MUE-001">
                    </div>

                    <div class="col-12">
                        <label class="form-label">No. Especímenes</label>
                        <select name="num_especimenes" class="form-select">
                            <option value="" selected disabled>--</option>
                            @for($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Fecha de recepción</label>
                        <input type="date" name="fecha_recepcion" id="fecha_recepcion" class="form-control"
                            value="{{ old('fecha_recepcion', date('Y-m-d')) }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Elemento</label>
                        <select name="elemento" class="form-select">
                            <option value="" selected disabled>Seleccione...</option>
                            @foreach($elementos as $elemento)
                            <option value="{{ $elemento->id }}">{{ $elemento->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Localización</label>
                        <input type="text" name="localizacion" class="form-control" placeholder="Ej. Eje 3, Nivel 2">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Fecha de muestreo</label>
                        <input type="date" name="fecha_muestreo" id="fecha_muestreo" class="form-control"
                            value="{{ old('fecha_muestreo', date('Y-m-d')) }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Datos de proyecto</label>
                        <textarea name="datos_proyecto" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Defectos del espécimen</label>
                        <textarea name="defectos_especimen" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label d-block">Fechas de ensayo</label>
                        <div class="d-flex flex-column gap-2">
                            @foreach([1, 3, 7, 14, 28] as $dia)
                            <div class="ensayo-fila d-flex align-items-center justify-content-between">
                                <div class="form-check d-flex align-items-center gap-2 mb-0">
                                    <input class="form-check-input" type="checkbox" name="fechas_ensayo[]"
                                        value="{{ $dia }}" id="ensayo{{ $dia }}" data-dias="{{ $dia }}">
                                    <label class="form-check-label mb-0" for="ensayo{{ $dia }}">
                                        {{ $dia }}
                                    </label>
                                </div>
                                <span class="fecha-aproximada-box" id="fecha-ensayo-{{ $dia }}">--</span>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Envía</label>
                        <input type="text" name="envia" class="form-control" placeholder="Nombre de quien envía">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Recibe</label>
                        <input type="text" name="recibe" class="form-control" placeholder="Nombre de quien recibe"
                            value="{{ old('recibe', $nombreCompletoUsuario) }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label text-muted">Usuario que registra</label>
                        <input type="text" class="form-control" value="{{ $usuario->usuario ?? 'N/A' }}" disabled>
                    </div>

                    <div class="col-12">
                        <label class="form-label text-muted">Fecha de registro</label>
                        <input type="text" class="form-control" value="{{ date('d/m/Y') }}" disabled>
                        <small class="text-muted">La hora exacta se guarda automáticamente al enviar el formulario.</small>
                    </div>

                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-guardar">Guardar Recepción</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputMuestreo = document.getElementById('fecha_muestreo');
        const checks = document.querySelectorAll('#formRecepcion input[name="fechas_ensayo[]"]');

        function formatearFecha(date) {
            const dia = String(date.getDate()).padStart(2, '0');
            const mes = String(date.getMonth() + 1).padStart(2, '0');
            const anio = date.getFullYear();
            return `${dia}/${mes}/${anio}`;
        }

        function actualizarFechasEnsayo() {
            const baseValue = inputMuestreo.value; // formato YYYY-MM-DD
            checks.forEach(function(check) {
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

        checks.forEach(function(check) {
            check.addEventListener('change', actualizarFechasEnsayo);
        });

        inputMuestreo.addEventListener('change', actualizarFechasEnsayo);

        // Por si el navegador recuerda checks marcados al recargar la página
        actualizarFechasEnsayo();
    });
</script>
@endsection