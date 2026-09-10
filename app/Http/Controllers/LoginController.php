<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function login()
    {
        if (session()->has('sesionUsuario')) {
            return redirect()->route("inicio");
        }

        return response()
            ->view("pages.Login", ["titulo" => "Login"])
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 1990 00:00:00 GMT');
    }

    public function login_usuario(Request $request)
    {
        $request->validate([
            "usuario" => "required|string|regex:/^[A-Za-z0-9]+$/",
            "password" => "required|string"
        ]);

        $consulta = User::where("usuario", $request->usuario)->first();

        if (!$consulta || !password_verify($request->password, $consulta->password)) {
            return back()
                ->withInput($request->only('usuario'))
                ->with('error', 'Usuario o contraseña incorrectos');
        }

        session()->put("sesionUsuario", $consulta->id);

        return redirect()->route("inicio");
    }

    public function cerrar_sesion(Request $request)
    {
        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route("login");
    }
}
