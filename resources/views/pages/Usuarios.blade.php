@extends('plantilla')
@section('contenido')

@section('navbar-title', 'Usuarios')
@section('back-route', route('inicio'))

@include('layouts.navbar')

<div class="container d-flex flex-column justify-content-center align-items-center">

    @if(session('exito'))
    <div class="alert alert-success mt-3 w-100">{{ session('exito') }}</div>
    @endif
    @if($errors->any())
    <div class="alert alert-danger mt-3 w-100">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="d-flex flex-wrap justify-content-center gap-2 mt-4">
        <a id="crear-usuario" class="btn d-flex flex-column justify-content-center align-items-center text-decoration-none"
            data-bs-toggle="modal" data-bs-target="#modalCrear">
            <i class="fa-solid fa-user-plus mb-1"></i>
            Creacion de usuario
        </a>
        <a id="editar-usuario" class="btn d-flex flex-column justify-content-center align-items-center text-decoration-none"
            data-bs-toggle="modal" data-bs-target="#modalEditar">
            <i class="fa-solid fa-user-edit mb-1"></i>
            Edicion de usuario
        </a>
    </div>

    <div class="d-flex flex-wrap justify-content-center gap-2 mt-4">
        <a id="eliminar-usuario" class="btn d-flex flex-column justify-content-center align-items-center text-decoration-none"
            data-bs-toggle="modal" data-bs-target="#modalEliminar">
            <i class="fa-solid fa-user-minus mb-1"></i>
            Eliminacion de usuario
        </a>
    </div>
</div>

{{-- ========== MODAL CREAR ========== --}}
<div class="modal fade" id="modalCrear" tabindex="-1" aria-labelledby="modalCrearLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content" method="POST" action="{{ route('usuario.guardar') }}">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="modalCrearLabel">Crear usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Apellido paterno</label>
                    <input type="text" name="AP_paterno" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Apellido materno</label>
                    <input type="text" name="AP_materno" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Área</label>
                    <select name="area" class="form-select" required>
                        <option value="" disabled selected>Selecciona...</option>
                        @foreach($areas as $area)
                        <option value="{{ $area }}">{{ $area }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Usuario</label>
                    <input type="text" name="usuario" class="form-control" required autocomplete="off">
                </div>
                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <div class="input-group">
                        <input type="password" name="password" class="form-control" minlength="6" required autocomplete="new-password">
                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            onclick="mostrarPassword(this)">
                            <i class="fa-solid fa-eye-slash"></i>
                        </button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirmar contraseña</label>
                    <div class="input-group">
                        <input type="password" name="password_confirmation" class="form-control" minlength="6" required autocomplete="new-password">
                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            onclick="mostrarPassword(this)">
                            <i class="fa-solid fa-eye-slash"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

{{-- ========== MODAL EDITAR ========== --}}
<div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content" method="POST" action="{{ route('usuario.editar') }}">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarLabel">Editar usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Selecciona el usuario</label>
                    <select id="editar-select" name="id" class="form-select" required>
                        <option value="" disabled selected>Selecciona...</option>
                        @foreach($usuarios as $u)
                        <option value="{{ $u->id }}"
                            data-nombre="{{ $u->nombre }}"
                            data-paterno="{{ $u->AP_paterno }}"
                            data-materno="{{ $u->AP_materno }}"
                            data-area="{{ $u->area }}"
                            data-usuario="{{ $u->usuario }}">
                            {{ $u->nombre }} {{ $u->AP_paterno }} {{ $u->AP_materno }} ({{ $u->usuario }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div id="editar-campos" class="d-none">
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" id="editar-nombre" name="nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Apellido paterno</label>
                        <input type="text" id="editar-paterno" name="AP_paterno" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Apellido materno</label>
                        <input type="text" id="editar-materno" name="AP_materno" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Área</label>
                        <select id="editar-area" name="area" class="form-select" required>
                            @foreach($areas as $area)
                            <option value="{{ $area }}">{{ $area }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Usuario</label>
                        <input type="text" id="editar-usuario-input" name="usuario" class="form-control" required autocomplete="off">
                    </div>
                    <hr>
                    <p class="small text-muted mb-2">Deja la contraseña vacía si no deseas cambiarla.</p>
                    <div class="mb-3">
                        <label class="form-label">Nueva contraseña</label>
                        <div class="input-group">
                            <input type="password" name="password" class="form-control" minlength="6" autocomplete="new-password">
                            <button
                                type="button"
                                class="btn btn-outline-secondary"
                                onclick="mostrarPassword(this)">
                                <i class="fa-solid fa-eye-slash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirmar nueva contraseña</label>
                        <div class="input-group">
                            <input type="password" name="password_confirmation" class="form-control" minlength="6" autocomplete="new-password">
                            <button
                                type="button"
                                class="btn btn-outline-secondary"
                                onclick="mostrarPassword(this)">
                                <i class="fa-solid fa-eye-slash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>

{{-- ========== MODAL ELIMINAR ========== --}}
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" action="{{ route('usuario.eliminar') }}">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="modalEliminarLabel">Eliminar usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Selecciona el usuario</label>
                    <select name="id" class="form-select" required>
                        <option value="" disabled selected>Selecciona...</option>
                        @foreach($usuarios as $u)
                        <option value="{{ $u->id }}">{{ $u->nombre }} {{ $u->AP_paterno }} {{ $u->AP_materno }} ({{ $u->usuario }})</option>
                        @endforeach
                    </select>
                </div>
                <p class="text-danger mb-0">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger">Eliminar</button>
            </div>
        </form>
    </div>
</div>

<script src="{{ asset('js/pages/Usuarios.js') }}"></script>

@endsection