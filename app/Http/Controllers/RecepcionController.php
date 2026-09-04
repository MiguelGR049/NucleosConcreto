<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class RecepcionController extends Controller
{
    public function crear()
    {
        // Datos de ejemplo (temporales, luego vendrán de la BD)
        $obras = [
            (object)['id' => 1, 'clave' => 'OB-001'],
            (object)['id' => 2, 'clave' => 'OB-002'],
        ];

        $elementos = [
            (object)['id' => 1, 'nombre' => 'Columna'],
            (object)['id' => 2, 'nombre' => 'Losa'],
            (object)['id' => 3, 'nombre' => 'Trabe'],
            (object)['id' => 4, 'nombre' => 'Muro'],
        ];

        $folioGenerado = 'A1B2C3'; // temporal, luego se generará dinámico

        $usuario = User::find(session('sesionUsuario'));

        return response()
            ->view("pages.Recepcion", [
                "titulo" => "Recepción de Núcleos",
                "obras" => $obras,
                "elementos" => $elementos,
                "folioGenerado" => $folioGenerado,
                "usuario" => $usuario,
            ])
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 1990 00:00:00 GMT');
    }
}