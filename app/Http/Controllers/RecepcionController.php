<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Elemento;
use App\Models\Recepcion;
use App\Models\EnsayoNucleo;
use App\Models\ListadoObra;
use App\Models\FechaEnsayo;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RecepcionController extends Controller
{
    public function crear()
    {
        if (!session()->has('sesionUsuario')) {
            return redirect()->route("login");
        }

        $elementos = Elemento::orderBy('nombre')->get();

        // Vista previa del siguiente folio (el definitivo se asigna en store())
        $folioGenerado = $this->generarFolio();

        $usuario = User::find(session('sesionUsuario'));

        $nombreCompletoUsuario = $usuario
            ? trim("{$usuario->nombre} {$usuario->AP_paterno} {$usuario->AP_materno}")
            : '';

        return response()
            ->view("pages.Recepcion", [
                "titulo" => "Recepción de Núcleos",
                "elementos" => $elementos,
                "folioGenerado" => $folioGenerado,
                "usuario" => $usuario,
                "nombreCompletoUsuario" => $nombreCompletoUsuario,
            ])
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 1990 00:00:00 GMT');
    }

    public function buscarObra(Request $request)
    {
        if (!session()->has('sesionUsuario')) {
            return response()->json(['encontrada' => false], 401);
        }

        $clave = trim($request->query('clave', ''));

        $obra = $clave !== ''
            ? ListadoObra::where('CLAVE_OBRA', $clave)->first()
            : null;

        if (!$obra) {
            return response()->json(['encontrada' => false]);
        }

        return response()->json([
            'encontrada' => true,
            'cliente'    => $obra->EMPRESA,
            'obra'       => $obra->NOMBRE_OBRA,
            'atencion_a' => $obra->CON_ATENCION,
        ]);
    }

    public function store(Request $request)
    {
        if (!session()->has('sesionUsuario')) {
            return redirect()->route("login");
        }

        $validated = $request->validate([
            'clave_obra' => 'required|string|max:50',
            'cliente' => 'nullable|string|max:100',
            'obra' => 'nullable|string|max:100',
            'atencion_a' => 'nullable|string|max:100',
            'numero_informe' => 'nullable|string|max:50',
            'fecha_recepcion' => 'required|date',
            'num_especimenes' => 'required|integer|min:1|max:5',
            'especimenes' => 'required|array|min:1|max:5',
            'especimenes.*.numero_muestra' => 'required|string|max:50',
            'especimenes.*.elemento'       => 'required|string|max:100',
            'especimenes.*.espesor'        => 'required|numeric|min:0|max:999999.99',
            'especimenes.*.localizacion'   => 'required|string|max:150',
            'fecha_muestreo' => 'required|date',
            'fecha_colado' => 'nullable|date',
            'datos_proyecto' => 'nullable|string',
            'defectos_especimen' => 'nullable|string',
            'fechas_ensayo' => 'nullable|array',
            'fechas_ensayo.*' => 'integer|in:1,3,7,14,28',
            'envia' => 'nullable|string|max:100',
            'recibe' => 'nullable|string|max:100',
            'observaciones' => 'nullable|string',
        ]);

        // Regla de negocio: no más fechas de ensayo que especímenes
        if (!empty($validated['fechas_ensayo']) && count($validated['fechas_ensayo']) > $validated['num_especimenes']) {
            return back()->withInput()->withErrors([
                'fechas_ensayo' => 'No puedes seleccionar más fechas de ensayo que especímenes registrados.',
            ]);
        }

        if (count($validated['especimenes']) !== (int) $validated['num_especimenes']) {
            return back()->withInput()->withErrors([
                'especimenes' => 'La cantidad de especímenes capturados no coincide con "No. Especímenes".',
            ]);
        }

        $fechaMuestreo = Carbon::parse($validated['fecha_muestreo']);

        $fechasCalculadas = [];
        foreach ($validated['fechas_ensayo'] ?? [] as $dia) {
            $fechaCalculada = $fechaMuestreo->copy()->addDays((int) $dia);
            $fechasCalculadas[] = ['dia' => (int) $dia, 'fecha_ensayo' => $fechaCalculada];
        }

        try {
            $folio = DB::transaction(function () use ($validated, $fechasCalculadas) {
                // El folio se genera dentro de la transacción para evitar duplicados
                $folio = $this->generarFolio();

                $recepcion = Recepcion::create([
                    'folio' => $folio,
                    'clave_obra' => $validated['clave_obra'],
                    'cliente' => $validated['cliente'] ?? null,
                    'obra' => $validated['obra'] ?? null,
                    'atencion_a' => $validated['atencion_a'] ?? null,
                    'n_informe' => $validated['numero_informe'] ?? null,
                    'fecha_rec' => $validated['fecha_recepcion'],
                    'n_espec' => $validated['num_especimenes'],
                    'datos_proy' => $validated['datos_proyecto'] ?? null,
                    'defectos' => $validated['defectos_especimen'] ?? null,
                    'envia' => $validated['envia'] ?? null,
                    'recibe' => $validated['recibe'] ?? null,
                    'fecha_muest' => $validated['fecha_muestreo'],
                    'fecha_colado' => $validated['fecha_colado'],
                    'observ' => $validated['observaciones'] ?? null,
                    'usuario_reg_id' => session('sesionUsuario'),
                    'fecha_reg' => now(),
                ]);

                foreach ($validated['especimenes'] as $e) {
                    EnsayoNucleo::create([
                        'recepcion_id' => $recepcion->id_seg,
                        'no_nucleo'    => $e['numero_muestra'],
                        'elemento'     => $e['elemento'],
                        'espesor'      => $e['espesor'],
                        'localizacion' => $e['localizacion'],
                    ]);

                    // Si el elemento no existe en el catálogo, lo agregamos
                    Elemento::firstOrCreate(['nombre' => strtoupper(trim($e['elemento']))]);
                }

                foreach ($fechasCalculadas as $f) {
                    FechaEnsayo::create([
                        'recepcion_id' => $recepcion->id_seg,
                        'dia'          => $f['dia'],
                        'fecha_ensayo' => $f['fecha_ensayo'],
                        'capturada'    => false,  // nueva muestra: pendiente de captura
                        'ensayada'     => false,   // nueva muestra: pendiente de ensayar
                    ]);
                }

                return $folio;
            });
        } catch (\RuntimeException $e) {
            // Por ejemplo, se agotaron los folios del mes
            return back()->withInput()->withErrors(['folio' => $e->getMessage()]);
        }

        return redirect()->route('inicio')
            ->with('success', "Recepción registrada correctamente. Folio: {$folio}");
    }

    /**
     * Genera el siguiente folio consecutivo del mes.
     * Formato: [1-9][A-Z][AA][MMM]  ->  1A26OCT, 2A26OCT ... 9A26OCT, 1B26OCT ... 9Z26OCT
     * Se reinicia en 1A al cambiar de mes.
     */
    private function generarFolio(): string
    {
        $meses = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];

        $ahora  = Carbon::now();
        $sufijo = $ahora->format('y') . $meses[$ahora->month - 1]; // ej. 26OCT

        // Último folio del mes actual: primero por letra, luego por dígito
        $ultimo = Recepcion::where('folio', 'like', '__' . $sufijo)
            ->orderByRaw('SUBSTRING(folio, 2, 1) DESC')
            ->orderByRaw('SUBSTRING(folio, 1, 1) DESC')
            ->lockForUpdate()
            ->value('folio');

        // Primer folio del mes
        if (!$ultimo) {
            return '1A' . $sufijo;
        }

        $digito = (int) $ultimo[0];
        $letra  = $ultimo[1];

        if ($digito < 9) {
            return ($digito + 1) . $letra . $sufijo;   // 1A -> 2A ... 8A -> 9A
        }

        if ($letra === 'Z') {
            throw new \RuntimeException("Se agotaron los folios de {$sufijo} (9Z alcanzado).");
        }

        return '1' . chr(ord($letra) + 1) . $sufijo;   // 9A -> 1B
    }
}
