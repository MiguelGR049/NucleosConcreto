<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Informe {{ $d['folio'] ?? '' }}</title>
    <style>
        @page {
            size: letter;
            margin: 0;
        }

        body {
            margin: 0;
            font-family: Helvetica, Arial, sans-serif;
            font-size: 6.9pt;
            color: #000;
            background: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .pagina {
            position: relative;
            width: 612pt;
            height: 792pt;
            page-break-after: always;
            overflow: hidden;
            background: #fff;
        }

        .pagina.ultima {
            page-break-after: auto;
        }

        .abs {
            position: absolute;
        }

        .titulo {
            text-align: center;
            font-weight: bold;
            font-size: 11.2pt;
            line-height: 15.8pt;
        }

        .codigo {
            text-align: center;
            font-size: 7.6pt;
            line-height: 11pt;
        }

        table {
            border-collapse: collapse;
            table-layout: fixed;
            width: 100%;
        }

        td {
            padding: 1pt 2pt;
            vertical-align: middle;
            overflow: hidden;
            white-space: nowrap;
            font-size: 6.9pt;
        }

        tr.sp td {
            height: 0;
            padding: 0;
            border: 0;
            line-height: 0;
            font-size: 0;
        }

        sup {
            font-size: 4.2pt;
            line-height: 0;
        }

        .b {
            font-weight: bold;
        }

        .az {
            background: #538DD5;
            font-weight: bold;
            text-align: left;
            color: #000;
        }

        .cl {
            background: #C5D9F1;
            font-weight: bold;
            text-align: left;
            color: #000;
        }

        .c {
            text-align: center;
        }

        .s {
            font-size: 6.3pt;
        }

        .gb {
            border-bottom: 0.7pt solid #A6A6A6;
        }

        .gt {
            border-top: 0.7pt solid #A6A6A6;
        }

        .ab {
            border-bottom: 0.8pt solid #0070C0;
        }

        .at {
            border-top: 0.8pt solid #0070C0;
        }

        .calc {
            background: #DDDDDD;
        }

        .enc td {
            padding: 1.5pt 2pt;
        }

        .res td {
            padding: 1pt 2pt;
            font-weight: bold;
            text-align: center;
        }

        .res td.cl {
            font-size: 6.0pt;
            text-align: left;
        }

        .pie td {
            border-top: 0.7pt solid #A6A6A6;
            padding: 3pt 2pt;
        }

        .pie tr.ult td {
            border-bottom: 0.7pt solid #A6A6A6;
        }

        .pie td.az,
        .pie td.cl {
            text-align: center;
        }
    </style>
</head>

<body>

    @php
    $filas = [
    ['no_nucleo', 'No. DE MUESTRA / NÚCLEO', false],
    ['elemento', 'ELEMENTO', false],
    ['localizacion', 'LOCALIZACIÓN', false],
    ['fecha_colado', 'FECHA DE COLADO', false],
    ['espesor', 'ESPESOR, mm', false],
    ['edad', 'EDAD DE ENSAYE, DÍAS', true],
    ['masa', 'MASA DEL ESPÉCIMEN, g', false],
    ['masa_vol', 'MASA VOLUMÉTRICA, kg·m<sup>-3</sup>', true],
    ['d1', 'DIÁMETRO 1, mm', false],
    ['d2', 'DIÁMETRO 2, mm', false],
    ['dprom', 'DIÁMETRO PROMEDIO, mm', true],
    ['a1s', 'ALTURA 1 SIN CABECEO, mm', false],
    ['a2s', 'ALTURA 2 SIN CABECEO, mm', false],
    ['apsin', 'ALTURA PROMEDIO SIN CABECEO, mm', true],
    ['a1c', 'ALTURA 1 CON CABECEO, mm', false],
    ['a2c', 'ALTURA 2 CON CABECEO, mm', false],
    ['apcon', 'ALTURA PROMEDIO CON CABECEO, mm', true],
    ['esb', 'RELACIÓN DE ESBELTEZ, (h/d)', true],
    ['factor', 'FACTOR DE CORRECCIÓN', true],
    ['area', 'ÁREA, cm<sup>-2</sup>', true],
    ['kn', 'CARGA, kN', false],
    ['kg', 'CARGA, kg', true],
    ['falla', 'TIPO DE FALLA', false],
    ['directo', 'ESFUERZO DIRECTO, kg·cm<sup>-2</sup>', true],
    ['real', 'ESFUERZO REAL, kg·cm<sup>-2</sup>', true],
    ['mpa', 'ESFUERZO REAL, MPa', true],
    ['u', 'U (±), MPa', true],
    ];
    @endphp

    @foreach($d['paginas'] as $p)
    <div class="pagina {{ $loop->last ? 'ultima' : '' }}">

        @if($conImagenes)
        <img class="abs" src="{{ public_path('img/pdf/logo.jpeg') }}" style="left:42.5pt;  top:11.3pt;  width:63.7pt;  height:58.4pt;">
        <img class="abs" src="{{ public_path('img/pdf/barra.jpeg') }}" style="left:376.8pt; top:11.3pt;  width:192.7pt; height:14.3pt;">
        <img class="abs" src="{{ public_path('img/pdf/pie.jpeg') }}" style="left:42.4pt;  top:724.1pt; width:526.5pt; height:56.6pt;">
        @endif

        {{-- TÍTULO --}}
        <div class="abs titulo" style="left:58.9pt; top:37.3pt; width:493.3pt;">INFORME DE ENSAYO DE COMPRESIÓN DE<br>NÚCLEOS DE CONCRETO</div>
        <div class="abs codigo" style="left:58.9pt; top:67.9pt; width:493.3pt;">CON-LAB-07</div>

        {{-- ENCABEZADO --}}
        <div class="abs" style="left:58.9pt; top:86.8pt; width:493.3pt;">
            <table class="enc">
                <tr class="sp">
                    <td style="width:70pt"></td>
                    <td style="width:247.9pt"></td>
                    <td style="width:90.8pt"></td>
                    <td style="width:45.4pt"></td>
                    <td style="width:45.3pt"></td>
                </tr>
                <tr>
                    <td colspan="2" class="gb"></td>
                    <td class="az gb gt">INFORME:</td>
                    <td class="gb gt" style="text-align:center; padding-left:5pt;">LIE. RC.</td>
                    <td class="gb gt" style="text-align:center; padding-right:5pt;">{{ $d['n_informe'] ?? '' }}</td>
                </tr>
                <tr>
                    <td class="az s gb">FECHA DE INFORME: </td>
                    <td class="gb" style="text-align:center;">{{ $d['fecha_informe'] ?? '' }}</td>
                    <td class="az gb">CLAVE DE OBRA:</td>
                    <td class="gb b" colspan="2" style="text-align:center;">{{ $d['clave_obra'] ?? '' }}</td>
                </tr>
                <tr>
                    <td class="az gb">CLIENTE:</td>
                    <td class="gb b" colspan="4" style="text-align:center;">{{ $d['cliente'] ?? '' }}</td>
                </tr>
                <tr>
                    <td class="az gb">OBRA:</td>
                    <td class="gb b" colspan="4" style="text-align:center;">{{ $d['obra'] ?? '' }}</td>
                </tr>
                <tr>
                    <td class="az gb">PROCEDENCIA:</td>
                    <td class="gb" colspan="4" style="text-align:center;">{{ $d['procedencia'] ?? '' }}</td>
                </tr>
                <tr>
                    <td class="az s gb">ELEMENTO:</td>
                    <td class="gb" style="text-align:center;">{{ $d['elemento'] ?? '' }}</td>
                    <td class="az gb">FECHA DE EXTRACCIÓN:</td>
                    <td class="gb" colspan="2" style="text-align:center;">{{ $d['fecha_extraccion'] ?? '' }}</td>
                </tr>
                <tr>
                    <td class="az s gb">CON ATENCIÓN A:</td>
                    <td class="gb b" style="text-align:center;">{{ $d['atencion_a'] ?? '' }}</td>
                    <td class="az gb">MUESTREADO POR:</td>
                    <td class="gb" colspan="2" style="text-align:center;">{{ $d['muestreado_por'] ?? '' }}</td>
                </tr>
            </table>
        </div>

        {{-- DATOS DE PROYECTO --}}
        <div class="abs" style="left:122.6pt; top:184.0pt; width:384.3pt;">
            <table>
                <tr class="sp">
                    <td style="width:112.1pt"></td>
                    <td style="width:45.3pt"></td>
                    <td style="width:90.8pt"></td>
                    <td style="width:45.2pt"></td>
                    <td style="width:45.5pt"></td>
                    <td style="width:45.4pt"></td>
                </tr>
                <tr>
                    <td colspan="6" class="az c at ab" style="text-align:center;">DATOS DE PROYECTO</td>
                </tr>
                <tr>
                    <td class="cl c ab" style="text-align:center;">T. N., mm:</td>
                    <td class="ab c">{{ $d['tn'] ?? '' }}</td>
                    <td class="cl c ab" style="text-align:center;">REVENIMIENTO, cm</td>
                    <td class="ab c">{{ $d['revenimiento'] ?? '' }}</td>
                    <td class="cl c" rowspan="2" style="text-align:center; font-size:5.5pt; white-space:normal; line-height:6.6pt; border-bottom:0.8pt solid #0070C0;">
                        RESISTENCIA DE<br>PROYECTO,<br>kg·cm<sup>-2</sup>
                    </td>
                    <td class="b c" rowspan="2" style="border-bottom:0.8pt solid #0070C0;">{{ $d['resistencia'] ?? '' }}</td>
                </tr>
                <tr>
                    <td class="cl c ab" style="text-align:center;">CONCRETO:</td>
                    <td class="ab c">{{ $d['concreto'] ?? '' }}</td>
                    <td class="cl c ab" style="text-align:center;">TIPO DE CEMENTO:</td>
                    <td class="ab c">{{ $d['cemento'] ?? '' }}</td>
                </tr>
            </table>
        </div>

        {{-- TABLA DE RESULTADOS --}}
        <div class="abs" style="left:58.9pt; top:233.6pt; width:493.3pt;">
            <table class="res">
                <tr class="sp">
                    <td style="width:143.0pt"></td>
                    <td style="width:87.58pt"></td>
                    <td style="width:87.58pt"></td>
                    <td style="width:87.58pt"></td>
                    <td style="width:87.56pt"></td>
                </tr>
                <tr>
                    <td colspan="5" class="az at ab" style="text-align:center; font-size:6.9pt;">TABLA DE RESULTADOS</td>
                </tr>
                @foreach($filas as [$clave, $etiqueta, $calculada])
                <tr>
                    <td class="cl ab">{!! $etiqueta !!}</td>
                    @for($c = 0; $c < 4; $c++)
                        <td class="ab {{ $calculada ? 'calc' : '' }}">{{ $p['nucleos'][$c][$clave] ?? '' }}</td>
                        @endfor
                </tr>
                @endforeach
            </table>
        </div>

        {{-- DECLARACIÓN, OBSERVACIONES, MÉTODOS Y REGLA --}}
        <div class="abs" style="left:58.9pt; top:546.8pt; width:493.3pt;">
            <table class="pie">
                <tr class="sp">
                    <td style="width:143.0pt"></td>
                    <td style="width:350.3pt"></td>
                </tr>
                <tr>
                    <td class="az">DECLARACIÓN DE CONFORMIDAD:</td>
                    <td class="b" style="text-align: center; padding-left: 4pt;">{{ $p['conformidad'] ?? '' }}</td>
                </tr>
                <tr>
                    <td class="cl">OBSERVACIONES:</td>
                    <td style="text-align: center; padding-left: 4pt;">{{ $d['observaciones'] ?? '' }}</td>
                </tr>
                <tr>
                    <td class="cl">MÉTODOS DE REFERENCIA:</td>
                    <td class="b" style="font-size:5.5pt; line-height:8.3pt; text-align: center; padding-left: 4pt;">
                        ASTM C 42/C42M-18a, ASTM C617/C617M-15 Y ASTM C39/C39M-16b,<br>
                        PLAN DE MUESTREO "LIEP-04b MUESTREO"
                    </td>
                </tr>
                <tr class="ult">
                    <td class="az">REGLA DE DECISIÓN:</td>
                    <td style="font-size:6.3pt; line-height:9.1pt; white-space:normal; text-align: center; padding: 2pt 4pt;">
                        Considerando U con k=2, <b>CUMPLE</b> – El resultado se encuentra dentro del AL de la especificación;
                        <b>NO CUMPLE</b> – Alguna (s) de la determinación (s) no se encuentra dentro del TL.
                        (AL: Límite de aceptación - TL: Límite de tolerancia).
                    </td>
                </tr>
            </table>
        </div>

        {{-- FIRMAS --}}
        <div class="abs b" style="left:58.9pt;  top:653.6pt; width:175.8pt; text-align:center; font-size:6.3pt; line-height:11pt;">REALIZÓ/APROBÓ:</div>
        <div class="abs b" style="left:416.0pt; top:653.6pt; width:136.2pt; text-align:center; font-size:6.3pt; line-height:11pt;">FIRMA DEL CLIENTE:</div>

        <div class="abs" style="left:58.9pt;  top:695.9pt; width:175.8pt; border-top:.7pt solid #000;"></div>
        <div class="abs" style="left:416.0pt; top:695.9pt; width:136.2pt; border-top:.7pt solid #000;"></div>

        <div class="abs b" style="left:58.9pt;  top:697.2pt; width:175.8pt; text-align:center; font-size:6.9pt; line-height:11pt;">NOMBRE Y FIRMA</div>
        <div class="abs b" style="left:416.0pt; top:697.2pt; width:136.2pt; text-align:center; font-size:6.9pt; line-height:11pt;">NOMBRE Y FIRMA</div>

        <div class="abs" style="left:58.9pt; top:707.4pt; width:492.0pt; text-align:right; font-size:5.5pt; line-height:11pt;">“ESTE REPORTE SOLO REPRESENTA A LA(S) MUESTRA(S) ENSAYADA(S)”</div>

    </div>
    @endforeach

</body>

</html>