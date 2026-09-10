<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConsultaController extends Controller
{
    public function consulta(){
        if (!session()->has('sesionUsuario')) {
            return redirect()->route("login");
        }

        return response()
            ->view("pages.Consulta", [
                "titulo" => "Consulta",
            ])
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 1990 00:00:00 GMT');
        
    }
}
