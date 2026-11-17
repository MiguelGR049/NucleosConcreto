<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SoloAdministracion
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('sesionUsuario')) {
            return redirect()->route('login'); // ajusta al nombre de tu ruta de login
        }

        if (session('areaUsuario') !== 'Administracion') {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}