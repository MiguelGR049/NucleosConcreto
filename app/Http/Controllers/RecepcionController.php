<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class RecepcionController extends Controller
{
    public function crear()
    {
        if (!session()->has('sesionUsuario')) {
            return redirect()->route("login");
        }

        $elementos = [
            (object)['id' => 1, 'nombre' => 'COLUMNA'],
            (object)['id' => 2, 'nombre' => 'LOSA'],
            (object)['id' => 3, 'nombre' => 'TRABE'],
            (object)['id' => 4, 'nombre' => 'MURO'],
        ];

        $folioGenerado = 'A1B2C3';

        $usuario = User::find(session('sesionUsuario'));

        $nombreCompletoUsuario = $usuario
            ? trim("{$usuario->nombre} {$usuario->AP_paterno} {$usuario->AP_materno}")
            : '';

        return response()
            ->view("pages.Recepcion", [
                "titulo" => "Recepción de Núcleos",
                "elementos" => $elementos,
                "folioGenerado" => $folioGenerado,
                "usuario" => $usuario,
                "nombreCompletoUsuario" => $nombreCompletoUsuario,
            ])
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 1990 00:00:00 GMT');
    }
}