<nav class="navbar shadow-sm">
    <div class="container-fluid d-flex align-items-center justify-content-between">

        <div class="navbar-left">
            @hasSection('back-route')
            <a href="@yield('back-route')" class="back-arrow-btn">
                <i class="bi bi-arrow-left"></i>
            </a>
            @endif
        </div>

        <span class="navbar-brand fw-semibold m-0 navbar-center">
            @yield('navbar-title', ' ')
        </span>

        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas"
            data-bs-target="#sidebarMenu" aria-controls="sidebarMenu"
            aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
    </div>
</nav>

<div class="offcanvas offcanvas-end" tabindex="-1" id="sidebarMenu" aria-labelledby="sidebarMenuLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="sidebarMenuLabel">
            @yield('navbar-title', ' ')
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link active" aria-current="page" href="{{ route('inicio') }}">
                    <i class="bi bi-house-door me-1"></i>Inicio
                </a>
            </li>
        </ul>

        <div class="mt-auto">
            <a href="#" class="btn cerrar-sesion-btn w-100" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                Cerrar sesión
            </a>
            <form id="logout-form" action="{{ route('cerrar_sesion') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>
    </div>
</div>