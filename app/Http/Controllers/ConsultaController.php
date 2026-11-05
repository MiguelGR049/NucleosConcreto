<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Browsershot\Browsershot;

class ConsultaController extends Controller
{
    public function consulta(Request $request)
    {
        if (!session()->has('sesionUsuario')) {
            return redirect()->route('login');
        }

        $q = strtoupper(trim($request->input('q', '')));
        $todas = $request->boolean('todas');

        $query = DB::table('recepciones')->orderByDesc('fecha_rec')->limit(30);

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('folio', $q)
                    ->orWhere('clave_obra', 'like', "%{$q}%");
            });
        }

        if (!$todas) {
            $query->whereExists(function ($s) {
                $s->select(DB::raw(1))
                    ->from('fechas_ensayo')
                    ->whereColumn('fechas_ensayo.recepcion_id', 'recepciones.id_seg')
                    ->where('fechas_ensayo.ensayada', 1);
            });
        }

        $resultados = $query->get()->map(function ($r) {
            $r->fechas = DB::table('fechas_ensayo')
                ->where('recepcion_id', $r->id_seg)
                ->orderBy('dia')
                ->get(['dia', 'fecha_ensayo', 'ensayada']);
            $r->total = $r->fechas->count();
            $r->ensayadas = $r->fechas->where('ensayada', 1)->count();

            // Datos crudos de los núcleos; las fórmulas se calculan en Consulta.js
            $r->nucleos = DB::table('ensayos_nucleo as n')
                ->join('hojas_ensayo as h', 'h.id_seg', '=', 'n.hoja_id')
                ->join('fechas_ensayo as f', 'f.id_seg', '=', 'h.fecha_ensayo_id')
                ->where('f.recepcion_id', $r->id_seg)
                ->orderBy('f.dia')
                ->orderBy('n.columna')
                ->select(
                    'n.*',
                    'h.tmn',
                    'h.resistencia_proyecto',
                    'f.dia',
                    'f.fecha_ensayo'
                )
                ->get();

            $r->elementos = DB::table('ensayos_nucleo')
                ->where('recepcion_id', $r->id_seg)
                ->distinct()
                ->orderBy('elemento')
                ->pluck('elemento')
                ->implode(', ');

            return $r;
        });

        return response()
            ->view("pages.Consulta", [
                "titulo" => "Consulta",
                "q" => $q,
                "todas" => $todas,
                "resultados" => $resultados,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    public function pdf(Request $request)
    {
        if (!session()->has('sesionUsuario')) {
            return redirect()->route('login');
        }

        $request->validate([
            'payload'  => 'required|json|max:200000',
            'variante' => 'required|in:con,sin',
        ]);

        $d = json_decode($request->input('payload'), true);
        abort_unless(is_array($d) && !empty($d['paginas']), 422);

        $conImagenes = $request->input('variante') === 'con';
        $folio = preg_replace('/[^A-Za-z0-9_-]/', '', $d['folio'] ?? 'informe');
        $sufijo = $conImagenes ? 'con-imagen' : 'sin-imagen';

        // Renderizamos la vista Blade en HTML plano para pasársela a Chrome
        $html = view('pages.ConsultaPdf', [
            'd' => $d,
            'conImagenes' => $conImagenes
        ])->render();

        // Generamos el PDF usando Browsershot (Chrome Headless)
        $pdfBinary = Browsershot::html($html)
            ->setOption('args', ['--no-sandbox', '--disable-setuid-sandbox'])
            ->format('Letter')
            ->setScreenshotType('pdf')
            ->margins(0, 0, 0, 0)
            ->pdf();

        return response()->streamDownload(
            fn() => print($pdfBinary),
            "Informe_{$folio}_{$sufijo}.pdf"
        );
    }
}
