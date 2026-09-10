<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EnsayoController extends Controller
{
    public function ensayo(){
        if (!session()->has('sesionUsuario')) {
            return redirect()->route("login");
        }

        return response()
            ->view("pages.Ensayo", [
                "titulo" => "Ensayo",
            ])
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 1990 00:00:00 GMT');
        
    }
}
