<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Corrección de datos de ensayos de núcleos (solo área Administracion).
 * El acceso lo protege el middleware 'admin' en las rutas.
 *
 * Supuestos sobre la base (ajústalos si tus nombres difieren):
 *  - hojas_ensayo:   PK id_seg, fecha_ensayo_id (FK a fechas_ensayo.id_seg)
 *  - fechas_ensayo:  PK id_seg, recepcion_id, dia, fecha_ensayo, ensayada
 *  - recepciones:    PK id_seg, folio, clave_obra
 *  - ensayos_nucleo: PK id_seg, hoja_id, columna, no_nucleo, elemento, espesor,
 *                    localizacion, masa_g, diametro_*, altura_*, carga_kn, tipo_falla
 *  - listado_obras:  CLAVE_OBRA, NOMBRE_OBRA
 */
class EdicionController extends Controller
{
    public function edicion(Request $request)
    {
        $q = strtoupper(trim((string) $request->query('q', '')));

        $query = DB::table('hojas_ensayo as h')
            ->join('fechas_ensayo as f', 'f.id_seg', '=', 'h.fecha_ensayo_id')
            ->join('recepciones as r', 'r.id_seg', '=', 'f.recepcion_id')
            ->select(
                'h.id_seg as hoja_id',
                'f.dia',
                'f.fecha_ensayo',
                'f.ensayada',
                'r.folio',
                'r.clave_obra',
                'r.obra'
            )
            ->orderByDesc('f.fecha_ensayo')
            ->orderBy('r.folio')
            ->where('r.eliminado', 0);

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('r.folio', 'like', "%{$q}%")
                    ->orWhere('r.clave_obra', 'like', "%{$q}%");
            });
        }

        $hojas = $query->limit($q === '' ? 20 : 60)->get();

        // Núcleos de cada hoja (cada uno es una columna del modal)
        $nucleos = DB::table('ensayos_nucleo')
            ->whereIn('hoja_id', $hojas->pluck('hoja_id'))
            ->orderBy('columna')
            ->get()
            ->groupBy('hoja_id');

        foreach ($hojas as $h) {
            $h->nucleos = ($nucleos[$h->hoja_id] ?? collect())->values();
        }

        return response()
            ->view('pages.Edicion', [
                'titulo' => 'Corrección de datos',
                'hojas' => $hojas,
                'q'     => $q,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function guardar(Request $request)
    {
        $data = $request->validate([
            'q'                                    => 'nullable|string|max:50',
            'hoja_id'                              => 'required|integer',
            'nucleos'                              => 'required|array|min:1|max:4',
            'nucleos.*.espesor'                    => 'nullable|numeric|min:0',
            'nucleos.*.masa_g'                     => 'required|numeric|min:0',
            'nucleos.*.diametro_1_mm'              => 'required|numeric|min:0',
            'nucleos.*.diametro_2_mm'              => 'required|numeric|min:0',
            'nucleos.*.altura_1_sin_cabeceo_mm'    => 'required|numeric|min:0',
            'nucleos.*.altura_2_sin_cabeceo_mm'    => 'required|numeric|min:0',
            'nucleos.*.altura_1_con_cabeceo_mm'    => 'nullable|numeric|min:0',
            'nucleos.*.altura_2_con_cabeceo_mm'    => 'nullable|numeric|min:0',
            'nucleos.*.carga_kn'                   => 'nullable|numeric|min:0',
            'nucleos.*.tipo_falla'                 => 'nullable|in:I,II,III,IV,V,VI',
        ], [], [
            'nucleos.*.espesor'                 => 'espesor',
            'nucleos.*.masa_g'                  => 'masa del espécimen',
            'nucleos.*.diametro_1_mm'           => 'diámetro 1',
            'nucleos.*.diametro_2_mm'           => 'diámetro 2',
            'nucleos.*.altura_1_sin_cabeceo_mm' => 'altura 1 sin cabeceo',
            'nucleos.*.altura_2_sin_cabeceo_mm' => 'altura 2 sin cabeceo',
            'nucleos.*.altura_1_con_cabeceo_mm' => 'altura 1 con cabeceo',
            'nucleos.*.altura_2_con_cabeceo_mm' => 'altura 2 con cabeceo',
            'nucleos.*.carga_kn'                => 'carga kN',
            'nucleos.*.tipo_falla'              => 'tipo de falla',
        ]);

        $hojaId = (int) $data['hoja_id'];

        // Solo se pueden editar núcleos que pertenezcan a esa hoja
        $idsValidos = DB::table('ensayos_nucleo')
            ->where('hoja_id', $hojaId)
            ->pluck('id_seg')
            ->map(fn($id) => (int) $id)
            ->all();

        abort_if(empty($idsValidos), 404);

        DB::transaction(function () use ($data, $hojaId, $idsValidos) {
            foreach ($data['nucleos'] as $id => $n) {
                if (!in_array((int) $id, $idsValidos, true)) {
                    continue;
                }

                DB::table('ensayos_nucleo')->where('id_seg', $id)->update([
                    'espesor'                  => $n['espesor'] ?? null,
                    'masa_g'                   => $n['masa_g'],
                    'diametro_1_mm'            => $n['diametro_1_mm'],
                    'diametro_2_mm'            => $n['diametro_2_mm'],
                    'altura_1_sin_cabeceo_mm'  => $n['altura_1_sin_cabeceo_mm'],
                    'altura_2_sin_cabeceo_mm'  => $n['altura_2_sin_cabeceo_mm'],
                    'altura_1_con_cabeceo_mm'  => $n['altura_1_con_cabeceo_mm'] ?? null,
                    'altura_2_con_cabeceo_mm'  => $n['altura_2_con_cabeceo_mm'] ?? null,
                    'carga_kn'                 => $n['carga_kn'] ?? null,
                    'tipo_falla'               => $n['tipo_falla'] ?? null,
                ]);
            }

            // Si borraron carga o falla de algún núcleo, la fecha vuelve a "pendiente";
            // si todos quedaron completos, queda como ensayada.
            $hayPendientes = DB::table('ensayos_nucleo')
                ->where('hoja_id', $hojaId)
                ->where(function ($w) {
                    $w->whereNull('carga_kn')->orWhereNull('tipo_falla');
                })
                ->exists();

            $fechaId = DB::table('hojas_ensayo')->where('id_seg', $hojaId)->value('fecha_ensayo_id');

            DB::table('fechas_ensayo')->where('id_seg', $fechaId)->update([
                'ensayada'   => $hayPendientes ? 0 : 1,
                'updated_at' => now(),
            ]);
        });

        return redirect()
            ->route('correccion', ['q' => $data['q'] ?? ''])
            ->with('success', 'Datos corregidos correctamente.');
    }

    public function eliminar(Request $request)
    {
        $data = $request->validate([
            'hoja_id' => 'required|integer',
            'q'       => 'nullable|string|max:50',
        ]);

        $recepcionId = DB::table('hojas_ensayo as h')
            ->join('fechas_ensayo as f', 'f.id_seg', '=', 'h.fecha_ensayo_id')
            ->where('h.id_seg', $data['hoja_id'])
            ->value('f.recepcion_id');

        abort_if(!$recepcionId, 404);

        DB::table('recepciones')->where('id_seg', $recepcionId)->update([
            'eliminado'         => 1,
            'fecha_eliminacion' => now(),
        ]);

        return redirect()
            ->route('correccion', ['q' => $data['q'] ?? ''])
            ->with('success', 'Registro eliminado. Se borrará de forma permanente en 30 días.');
    }
}
