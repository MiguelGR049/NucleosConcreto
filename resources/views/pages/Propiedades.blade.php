@extends('plantilla')
@section('contenido')

@section('navbar-title', 'Captura de propiedades')
@section('back-route', route('inicio'))

@include('layouts.navbar')

<div class="container-fluid px-3 px-md-4 mt-4 mb-4">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow propiedades-card">
                <div class="card-body p-3 p-md-4">

                    <h5 class="mb-3">Fechas de ensayo</h5>

                    {{-- Fecha + botón en la misma fila --}}
                    <div class="d-flex align-items-end gap-2 gap-md-3 mb-4">
                        <form method="GET" action="{{ route('propiedades') }}" class="flex-grow-1">
                            <input type="date" id="fecha" name="fecha"
                                class="form-control form-control-lg"
                                value="{{ $fecha }}" onchange="this.form.submit()">
                        </form>

                        <button type="submit" form="form-captura"
                            class="btn btn-lg btn-captura px-3 px-md-4 flex-shrink-0 text-nowrap">
                            <i class="bi bi-clipboard-check"></i>
                            <span class="d-none d-sm-inline ms-1">Captura</span>
                        </button>
                    </div>

                    {{-- Tabla --}}
                    <form id="form-captura" method="POST" action="{{ route('propiedades.guardar') }}">
                        @csrf
                        <input type="hidden" name="fecha" value="{{ $fecha }}">

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle text-center mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Clave</th>
                                        <th>Muestreo</th>
                                        <th>Observaciones</th>
                                        <th>Ensayar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($muestras as $m)
                                    <tr class="{{ $m->ensayada ? 'table-secondary text-muted' : '' }}">
                                        <td class="fw-semibold">{{ $m->clave }}</td>
                                        <td>{{ \Carbon\Carbon::parse($m->muestreo)->format('d/m/Y') }}</td>
                                        <td class="text-start">{{ $m->observaciones ?: '—' }}</td>
                                        <td>
                                            <input type="checkbox" class="form-check-input"
                                                name="ensayar[]" value="{{ $m->id_seg }}"
                                                {{ $m->ensayada ? 'checked disabled' : '' }}>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-muted py-4">No hay muestras para esta fecha</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

@endsection