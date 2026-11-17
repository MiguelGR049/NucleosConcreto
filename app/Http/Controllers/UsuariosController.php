<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuariosController extends Controller
{
    private function areas(): array
    {
        return ['TI', 'Administacion', 'Laboratorio']; // ajústala a tus áreas reales
    }

    public function usuario()
    {
        $usuarios = User::orderBy('nombre')->get();
        $areas = $this->areas();

        return view(
            'pages.Usuarios',
            [
                "titulo" => "Usuarios",
                "usuarios" => $usuarios,
                "areas" => $areas
            ]
        );
    }

    public function guardarUsuario(Request $request)
    {
        $data = $request->validate([
            'nombre'     => 'required|string|max:100',
            'AP_paterno' => 'required|string|max:100',
            'AP_materno' => 'nullable|string|max:100',
            'area'       => ['required', Rule::in($this->areas())],
            'usuario'    => 'required|string|max:50|regex:/^[A-Za-z0-9]+$/|unique:usuarios,usuario',
            'password'   => 'required|string|min:6|confirmed',
        ]);

        $data['password'] = Hash::make($data['password']);
        User::create($data);

        return redirect()->route('usuario')->with('exito', 'Usuario creado correctamente.');
    }

    public function editarUsuario(Request $request)
    {
        $data = $request->validate([
            'id'         => 'required|exists:usuarios,id',
            'nombre'     => 'required|string|max:100',
            'AP_paterno' => 'required|string|max:100',
            'AP_materno' => 'nullable|string|max:100',
            'area'       => ['required', Rule::in($this->areas())],
            'usuario'    => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9]+$/', Rule::unique('usuarios', 'usuario')->ignore($request->id)],
            'password'   => 'nullable|string|min:6|confirmed', // vacío = no cambiar
        ]);

        $user = User::findOrFail($data['id']);

        // Evita que un usuario TI se quite el área a sí mismo
        if ($user->id === (int) session('sesionUsuario') && $data['area'] !== 'TI') {
            return back()->withErrors(['area' => 'No puedes quitarte el área TI a ti mismo.']);
        }

        // Evita dejar el sistema sin ningún usuario TI
        if ($user->area === 'TI' && $data['area'] !== 'TI' && User::where('area', 'TI')->count() <= 1) {
            return back()->withErrors(['area' => 'Debe existir al menos un usuario TI.']);
        }

        unset($data['id']);

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return redirect()->route('usuario')->with('exito', 'Usuario actualizado correctamente.');
    }

    public function eliminarUsuario(Request $request)
    {
        $request->validate(['id' => 'required|exists:usuarios,id']);

        $user = User::findOrFail($request->id);

        // Evita que un usuario se elimine a sí mismo
        if ($user->id === (int) session('sesionUsuario')) {
            return back()->withErrors(['id' => 'No puedes eliminar tu propia cuenta.']);
        }

        // Evita eliminar al último usuario TI
        if ($user->area === 'TI' && User::where('area', 'TI')->count() <= 1) {
            return back()->withErrors(['id' => 'Debe existir al menos un usuario TI.']);
        }

        $user->delete();

        return redirect()->route('usuario')->with('exito', 'Usuario eliminado correctamente.');
    }
}
