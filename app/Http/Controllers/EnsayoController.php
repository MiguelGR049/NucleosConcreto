<?php

namespace App\Http\Controllers;

use App\Models\EnsayoNucleo;
use App\Models\FechaEnsayo;
use App\Models\HojaEnsayo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnsayoController extends Controller
{
    public function ensayo()
    {
        if (!session()->has('sesionUsuario')) {
            return redirect()->route('login');
        }

        // Consultar únicamente los núcleos que todavía están pendientes.
        $pendientes = DB::table('ensayos_nucleo as n')
            ->join('hojas_ensayo as h', 'h.id_seg', '=', 'n.hoja_id')
            ->join('fechas_ensayo as f', 'f.id_seg', '=', 'h.fecha_ensayo_id')
            ->join('recepciones as r', 'r.id_seg', '=', 'f.recepcion_id')
            ->where(function ($q) {
                $q->whereNull('n.carga_kn')
                    ->orWhereNull('n.tipo_falla');
            })
            ->select(
                'n.id_seg',
                'n.hoja_id',
                'n.columna',
                'n.no_nucleo',
                'n.elemento',
                'n.masa_g',
                'n.diametro_1_mm',
                'n.diametro_2_mm',
                'n.altura_1_sin_cabeceo_mm',
                'n.altura_2_sin_cabeceo_mm',
                'n.altura_1_con_cabeceo_mm',
                'n.altura_2_con_cabeceo_mm',
                'n.carga_kn',
                'n.tipo_falla',
                'f.fecha_ensayo',
                'r.folio',
                'r.clave_obra',
                'r.obra',
            )
            ->orderBy('f.fecha_ensayo', 'asc')
            ->orderBy('r.folio', 'asc')
            ->orderBy('n.columna', 'asc')
            ->get();

        return response()
            ->view('pages.Ensayo', [
                'titulo' => 'Ensayo',
                'pendientes' => $pendientes,
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
            'nucleo_id' => 'required|integer|exists:ensayos_nucleo,id_seg',
            'carga_kn' => 'required|numeric|gt:0|max:999999.9999',
            'tipo_falla' => 'required|string|max:50',
        ]);

        // Resultado del guardado: 'ya_completo', 'guardado' o 'fecha_completa'.
        $resultado = DB::transaction(function () use ($datos) {

            // Buscar y bloquear el núcleo existente.
            $nucleo = EnsayoNucleo::where('id_seg', $datos['nucleo_id'])
                ->lockForUpdate()
                ->firstOrFail();

            // Evitar sobrescribir un ensayo que ya está completo.
            if ($nucleo->carga_kn !== null && $nucleo->tipo_falla !== null) {
                return 'ya_completo';
            }

            // Actualizar el registro existente. No se crea otro núcleo.
            $nucleo->update([
                'carga_kn' => $datos['carga_kn'],
                'tipo_falla' => $datos['tipo_falla'],
            ]);

            // ¿Queda algún núcleo de la misma hoja sin ensayar?
            $faltan = EnsayoNucleo::where('hoja_id', $nucleo->hoja_id)
                ->where(function ($q) {
                    $q->whereNull('carga_kn')
                        ->orWhereNull('tipo_falla');
                })
                ->lockForUpdate()
                ->exists();

            if ($faltan) {
                return 'guardado';
            }

            // Todos los núcleos de la hoja ya fueron ensayados:
            // marcar la fecha de ensayo como ensayada.
            $hoja = HojaEnsayo::findOrFail($nucleo->hoja_id);

            FechaEnsayo::where('id_seg', $hoja->fecha_ensayo_id)
                ->update(['ensayada' => 1]);

            return 'fecha_completa';
        });

        if ($resultado === 'ya_completo') {
            return redirect()
                ->route('ensayo')
                ->with('info', 'Este núcleo ya había sido completado.');
        }

        return redirect()
            ->route('ensayo')
            ->with(
                'success',
                $resultado === 'fecha_completa'
                    ? 'Ensayo guardado. Todos los núcleos de esta fecha ya fueron ensayados.'
                    : 'Ensayo guardado correctamente.'
            );
    }
}
