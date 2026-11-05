<?php

namespace App\Http\Controllers;

use App\Models\EnsayoNucleo;
use App\Models\FechaEnsayo;
use App\Models\HojaEnsayo;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PropiedadesController extends Controller
{
    private const MAX_COLUMNAS = 4;   // columnas que tiene la hoja

    private const CAMPOS_MEDICION = [
        'masa_g',
        'diametro_1_mm',
        'diametro_2_mm',
        'altura_1_sin_cabeceo_mm',
        'altura_2_sin_cabeceo_mm',
        'altura_1_con_cabeceo_mm',
        'altura_2_con_cabeceo_mm',
    ];

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
                'r.datos_proy',
                'r.fecha_muest as muestreo',
                'r.observ as observaciones'
            )
            ->orderBy('f.capturada')
            ->orderBy('f.dia')
            ->orderBy('r.folio')
            ->get();

        // Cuántos núcleos se pueden capturar en cada fecha y cuáles están pendientes
        $muestras->each(function ($m) {
            $cap = $this->capacidad($m->recepcion_id);
            $m->restantes   = $cap['restantes'];
            $m->pendientes  = $cap['pendientes'];
            $m->nucleos     = $cap['nucleos'];
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

    public function guardar(Request $request)
    {
        if (!session()->has('sesionUsuario')) {
            return redirect()->route('login');
        }

        $datos = $request->validate([
            'fecha'                => 'required|date',
            'fecha_ensayo_id'      => 'required|integer|exists:fechas_ensayo,id_seg',
            'tmn'                  => 'nullable|numeric|min:0|max:999.99',
            'resistencia_proyecto' => 'nullable|numeric|min:0|max:99999.99',

            'nucleos'                 => 'required|array|min:1|max:' . self::MAX_COLUMNAS,
            'nucleos.*.id_nucleo'     => 'required|integer|distinct|exists:ensayos_nucleo,id_seg',
            'nucleos.*.masa_g'        => 'required|numeric|gt:0|max:99999999.99',
            'nucleos.*.diametro_1_mm' => 'required|numeric|gt:0|max:9999.99',
            'nucleos.*.diametro_2_mm' => 'required|numeric|gt:0|max:9999.99',
            'nucleos.*.altura_1_sin_cabeceo_mm' => 'required|numeric|gt:0|max:99999.99',
            'nucleos.*.altura_2_sin_cabeceo_mm' => 'required|numeric|gt:0|max:99999.99',
            'nucleos.*.altura_1_con_cabeceo_mm' => 'required|numeric|gt:0|max:99999.99',
            'nucleos.*.altura_2_con_cabeceo_mm' => 'required|numeric|gt:0|max:99999.99',
        ]);

        $fechaEnsayo = FechaEnsayo::findOrFail($datos['fecha_ensayo_id']);

        if ($fechaEnsayo->capturada) {
            return back()->withInput()->withErrors([
                'nucleos' => 'Esta fecha de ensayo ya fue capturada.',
            ]);
        }

        // Regla: cada fecha pendiente necesita al menos 1 núcleo
        $nucleos = array_values($datos['nucleos']);
        $cap     = $this->capacidad($fechaEnsayo->recepcion_id);

        if (count($nucleos) > $cap['max']) {
            return back()->withInput()->withErrors([
                'nucleos' => "Solo puedes capturar hasta {$cap['max']} núcleo(s) en esta fecha: "
                    . "quedan {$cap['restantes']} por ensayar y {$cap['pendientes']} fecha(s) pendiente(s).",
            ]);
        }

        try {
            DB::transaction(function () use ($datos, $nucleos, $fechaEnsayo) {
                $hoja = HojaEnsayo::create([
                    'fecha_ensayo_id'      => $fechaEnsayo->id_seg,
                    'tmn'                  => $datos['tmn'] ?? null,
                    'resistencia_proyecto' => $datos['resistencia_proyecto'] ?? null,
                    'usuario_captura_id'   => session('sesionUsuario'),
                ]);

                foreach ($nucleos as $i => $n) {
                    // Solo se puede usar un núcleo de ESTA recepción que siga pendiente
                    $afectadas = EnsayoNucleo::where('id_seg', $n['id_nucleo'])
                        ->where('recepcion_id', $fechaEnsayo->recepcion_id)
                        ->whereNull('hoja_id')
                        ->update(Arr::only($n, self::CAMPOS_MEDICION) + [
                            'hoja_id' => $hoja->id_seg,
                            'columna' => $i + 1,
                        ]);

                    if ($afectadas !== 1) {
                        throw new \RuntimeException('Uno de los núcleos no pertenece a esta recepción o ya fue capturado.');
                    }
                }

                $fechaEnsayo->update(['capturada' => 1]);
            });
        } catch (\RuntimeException $e) {
            return back()->withInput()->withErrors(['nucleos' => $e->getMessage()]);
        }

        return redirect()
            ->route('propiedades', ['fecha' => $datos['fecha']])
            ->with('success', 'Hoja de resultados guardada correctamente.');
    }

    /**
     * Cuántos núcleos se pueden capturar ahora en una fecha de la recepción.
     * Los núcleos pendientes son los que aún no tienen hoja (hoja_id null).
     * max = restantes - (fechas pendientes - 1), acotado a 0..4,
     * porque cada una de las demás fechas pendientes necesita al menos 1.
     */
    private function capacidad(int $recepcionId): array
    {
        $nucleos = DB::table('ensayos_nucleo')
            ->where('recepcion_id', $recepcionId)
            ->whereNull('hoja_id')
            ->orderBy('id_seg')
            ->get(['id_seg', 'no_nucleo', 'elemento', 'espesor', 'localizacion']);

        $pendientes = DB::table('fechas_ensayo')
            ->where('recepcion_id', $recepcionId)
            ->where('capturada', 0)
            ->count();

        $restantes = $nucleos->count();

        return [
            'restantes'  => $restantes,
            'pendientes' => $pendientes,
            'nucleos'    => $nucleos,
            'max'        => max(0, min(self::MAX_COLUMNAS, $restantes - max(0, $pendientes - 1))),
        ];
    }
}