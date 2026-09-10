@extends('plantilla')

@section('contenido')

<div class="container vh-100 justify-content-center align-items-center mt-5">
    <form method="post" action="{{ route('login.post') }}" class="w-100">
        @csrf
        <div class="card mx-auto">
            <div class="card-body">
                <img id="logo" src="{{ asset('img/logo.jpeg') }}" alt="Logo" class="img-fluid mb-4 d-block mx-auto">

                <h1 class="text-center fw-bold mb-4">Núcleos de Concreto</h1>

                @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
                @endif

                <div class="mb-3">
                    <label for="usuario" class="form-label fw-bold">Usuario</label>
                    <input type="text" name="usuario" id="usuario" class="form-control" value="{{ old('usuario') }}" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-bold">Contraseña</label>

                    <div class="input-group">
                        <input type="password" name="password" id="password" class="form-control" required>

                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            onclick="mostrarPassword()">
                            <i id="icono-ojo" class="fa-solid fa-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-success">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        Ingresar
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
    function mostrarPassword() {
        const password = document.getElementById('password');
        const iconoOjo = document.getElementById('icono-ojo');
        if (password.type === 'password') {
            password.type = 'text';
            iconoOjo.classList.remove('fa-eye-slash');
            iconoOjo.classList.add('fa-eye');
        } else {
            password.type = 'password';
            iconoOjo.classList.remove('fa-eye');
            iconoOjo.classList.add('fa-eye-slash');
        }
    }
</script>

@endsection