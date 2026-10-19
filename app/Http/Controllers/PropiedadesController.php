<?php

namespace App\Http\Controllers;

use App\Models\EnsayoNucleo;
use App\Models\FechaEnsayo;
use App\Models\HojaEnsayo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PropiedadesController extends Controller
{
    private const MAX_COLUMNAS = 4;   // columnas que tiene la hoja

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
                'f.capturada',
                'r.id_seg as recepcion_id',
                'r.folio',
                'r.clave_obra',
                'r.obra',
                'r.elemento',
                'r.localizacion',
                'r.datos_proy',
                'r.fecha_muest as muestreo',
                'r.observ as observaciones'
            )
            ->orderBy('f.capturada')
            ->orderBy('f.dia')
            ->orderBy('r.folio')
            ->get();

        // Cuántos núcleos se pueden capturar en cada fecha
        $muestras->each(function ($m) {
            $cap = $this->capacidad($m->recepcion_id);
            $m->restantes   = $cap['restantes'];
            $m->pendientes  = $cap['pendientes'];
            $m->max_nucleos = $m->capturada ? 0 : $cap['max'];
        });

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

    public function guardar(Request $request){
        if (!session()->has('sesionUsuario')) {
            return redirect()->route('login');
        }

        $datos = $request->validate([
            'fecha'                => 'required|date',
            'fecha_ensayo_id'      => 'required|integer|exists:fechas_ensayo,id_seg',
            'tmn'                  => 'nullable|numeric|min:0|max:999.99',
            'resistencia_proyecto' => 'nullable|numeric|min:0|max:99999.99',

            'nucleos'              => 'required|array|min:1|max:' . self::MAX_COLUMNAS,
            'nucleos.*.no_nucleo'  => 'required|string|max:20',
            'nucleos.*.masa_g'     => 'required|numeric|gt:0|max:99999999.99',
            'nucleos.*.diametro_1_mm' => 'required|numeric|gt:0|max:9999.99',
            'nucleos.*.diametro_2_mm' => 'required|numeric|gt:0|max:9999.99',
            'nucleos.*.altura_1_sin_cabeceo_mm' => 'required|numeric|gt:0|max:99999.99',
            'nucleos.*.altura_2_sin_cabeceo_mm' => 'required|numeric|gt:0|max:99999.99',
            'nucleos.*.altura_1_con_cabeceo_mm' => 'required|numeric|gt:0|max:99999.99',
            'nucleos.*.altura_2_con_cabeceo_mm' => 'required|numeric|gt:0|max:99999.99',
            'nucleos.*.carga_kn'   => 'nullable|numeric|gt:0|max:999999.99',
            'nucleos.*.tipo_falla' => 'nullable|string|max:50',
        ]);

        $fechaEnsayo = FechaEnsayo::findOrFail($datos['fecha_ensayo_id']);

        if ($fechaEnsayo->capturada) {
            return back()->withInput()->withErrors([
                'nucleos' => 'Esta fecha de ensayo ya fue capturada.',
            ]);
        }

        // Regla: cada fecha pendiente necesita al menos 1 núcleo
        $nucleos  = array_values($datos['nucleos']);
        $cap      = $this->capacidad($fechaEnsayo->recepcion_id);

        if (count($nucleos) > $cap['max']) {
            return back()->withInput()->withErrors([
                'nucleos' => "Solo puedes capturar hasta {$cap['max']} núcleo(s) en esta fecha: "
                    . "quedan {$cap['restantes']} por ensayar y {$cap['pendientes']} fecha(s) pendiente(s).",
            ]);
        }

        DB::transaction(function () use ($datos, $nucleos, $fechaEnsayo) {
            $hoja = HojaEnsayo::create([
                'fecha_ensayo_id'      => $fechaEnsayo->id_seg,
                'tmn'                  => $datos['tmn'] ?? null,
                'resistencia_proyecto' => $datos['resistencia_proyecto'] ?? null,
                'usuario_captura_id'   => session('sesionUsuario'),
            ]);
//
            foreach ($nucleos as $i => $n) {
                $n['carga_kn'] = null;
                    $n['tipo_falla'] = null;

                    EnsayoNucleo::create($n + [
                        'hoja_id' => $hoja->id_seg,
                        'columna' => $i + 1,
                    ]);
            }

            $fechaEnsayo->update(['capturada' => 1]);
        });

        return redirect()
            ->route('propiedades', ['fecha' => $datos['fecha']])
            ->with('success', 'Hoja de resultados guardada correctamente.');
    }

    /**
     * Cuántos núcleos se pueden capturar ahora en una fecha de la recepción.
     * max = especímenes restantes - (fechas pendientes - 1), acotado a 0..4,
     * porque cada una de las demás fechas pendientes necesita al menos 1.
     */
    private function capacidad(int $recepcionId): array
    {
        $especimenes = (int) DB::table('recepciones')
            ->where('id_seg', $recepcionId)
            ->value('n_espec');

        $capturados = DB::table('ensayos_nucleo as n')
            ->join('hojas_ensayo as h', 'h.id_seg', '=', 'n.hoja_id')
            ->join('fechas_ensayo as f', 'f.id_seg', '=', 'h.fecha_ensayo_id')
            ->where('f.recepcion_id', $recepcionId)
            ->count();

        $pendientes = DB::table('fechas_ensayo')
            ->where('recepcion_id', $recepcionId)
            ->where('capturada', 0)
            ->count();

        $restantes = $especimenes - $capturados;

        return [
            'restantes'  => $restantes,
            'pendientes' => $pendientes,
            'max'        => max(0, min(self::MAX_COLUMNAS, $restantes - max(0, $pendientes - 1))),
        ];
    }
}
