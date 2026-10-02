<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PropiedadesController extends Controller
{
    public function capturas(Request $request)
    {
        if (!session()->has('sesionUsuario')) {
            return redirect()->route('login');
        }

        $fecha = $request->query('fecha', now()->toDateString());

        $muestras = DB::table('fechas_ensayo as f')
            ->join('recepciones as r', 'r.id_seg', '=', 'f.recepcion_id')
            ->whereDate('f.fecha_ensayo', $fecha)
            ->select(
                'f.id_seg',
                'f.dia',
                'r.folio as clave',
                'r.fecha_muest as muestreo',
                'r.observ as observaciones',
                'f.ensayada'
            )
            ->orderBy('f.ensayada')   // pendientes primero, ensayadas al final
            ->orderBy('f.dia')        // de menor a mayor edad
            ->orderBy('r.folio')
            ->get();

        return response()
            ->view('pages.Propiedades', [
                'titulo'   => 'Captura de propiedades',
                'fecha'    => $fecha,
                'muestras' => $muestras,
            ])
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 1990 00:00:00 GMT');
    }

    public function guardar(Request $request)
    {
        if (!session()->has('sesionUsuario')) {
            return redirect()->route('login');
        }

        $ids = $request->input('ensayar', []);

        DB::table('fechas_ensayo')
            ->whereIn('id_seg', $ids)
            ->update(['ensayada' => 1, 'updated_at' => now()]);

        return redirect()->route('propiedades', ['fecha' => $request->input('fecha')]);
    }
}
