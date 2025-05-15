<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Requisición N° {{ $requisicion->numero_requisicion }}</title>
    <style>
        @page {
            margin-top: 120px;
            /* Espacio para el cintillo (ajusta según el alto de tu cintillo) */
            margin-bottom: 70px;
            /* Espacio para el pie de página (ajusta) */
            margin-left: 40px;
            margin-right: 40px;
        }

        body {
            font-family: 'Helvetica', Arial, sans-serif;
            /* O 'DejaVu Sans' si necesitas más caracteres Unicode */
            font-size: 9pt;
            line-height: 1.2;
            color: #000;
            position: relative;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            /* border: 1px solid #555; */
            padding: 3px 5px;
            /* vertical-align: top; */
        }

        th {
            /* background-color: #e0e0e0; */
            text-align: center;
            font-weight: bold;
            /* font-size: 10pt; */
        }

        .header-table,
        .footer-table {
            border: none;
        }

        .header-table td,
        .footer-table td {
            border: none;
            padding: 0;
        }

        .main-title {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: 5px;
        }

        .subtitle {
            font-size: 10pt;
            text-align: center;
            margin-bottom: 15px;
        }

        .section-title {
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 3px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .bold {
            font-weight: bold;
        }

        .logo {
            max-width: 180px;
            /* Ajusta según tu logo */
            max-height: 60px;
            /* Ajusta según tu logo */
            margin-bottom: 5px;
        }

        .info-box {
            border: 1px solid #000;
            padding: 5px;
        }

        .field-label {
            font-weight: bold;
        }

        .item-table td {
            font-size: 8pt;
        }

        .item-table th {
            font-size: 8pt;
        }

        /* Estilos para la Marca de Agua */
        .watermark-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1000;
            display: block;
            /* DOMPDF puede no interpretar flex bien para esto */
        }

        .watermark-text {
            position: absolute;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 72pt;
            color: rgba(253, 5, 5, 0.5);
            /* Muy tenue */
            font-weight: bold;
            text-transform: uppercase;
            white-space: nowrap;
            letter-spacing: 5px;
        }

        /* Para los checkboxes de TIPO */
        .checkbox-label {
            display: inline-block;
            /* Para que estén en línea */
            margin-right: 15px;
        }

        .checkbox {
            border: 1px solid #000;
            width: 10px;
            height: 10px;
            display: inline-block;
            text-align: center;
            line-height: 10px;
            /* Para centrar la X si la pones */
            margin-right: 3px;
            font-weight: bold;
        }

        .signature-block {
            margin-top: 30px;
            /* Espacio antes de las firmas */
            page-break-inside: avoid;
            /* Intentar no cortar el bloque de firmas */
        }

        .signature-box {
            display: inline-block;
            /* O usar tablas para layout */
            width: 30%;
            /* Ajusta según necesites */
            text-align: center;
            margin: 10px 1%;
            padding-top: 25px;
            border-top: 1px solid #000;
        }


        .page-break {
            page-break-after: always;
        }

        /* --- CABECERA (CINTILLO) --- */
        #header {
            position: fixed;
            top: -100px;
            /* Ajusta para que empiece desde arriba del margen superior y baje hasta el margen */
            left: 0px;
            /* Ajusta si tu @page tiene márgenes izquierdo/derecho diferentes de 0 */
            right: 0px;
            /* Ajusta si tu @page tiene márgenes izquierdo/derecho diferentes de 0 */
            width: 100%;
            /* O el ancho de tu cintillo si no es completo */
            text-align: center;
            /* O como necesites alinear la imagen */
            /* border-bottom: 1px solid #000; /* Opcional: una línea debajo del cintillo */
            /* padding-bottom: 5px; */
        }

        #header img {
            width: 100%;
            /* Para que ocupe todo el ancho disponible dentro de los márgenes */
            /* O un ancho fijo: width: 700px; */
            max-height: 80px;
            /* Ajusta al alto de tu cintillo */
            display: block;
            /* Evitar espacio extra debajo de la imagen */
            margin: 0 auto;
            /* Centrar si no es de ancho completo */
        }

        /* --- PIE DE PÁGINA --- */
        #footer {
            position: fixed;
            bottom: -50px;
            /* Ajusta para que empiece desde abajo del margen inferior y suba hasta el margen */
            left: 0px;
            right: 0px;
            width: 100%;
            text-align: center;
            font-size: 8pt;
            /* border-top: 1px solid #ccc; /* Opcional */
            /* padding-top: 5px; */
        }

        #footer .page-number:before {
            content: "Página " counter(page);
            /* Para numeración de página */
        }
    </style>
</head>

<body>

    {{-- CABECERA / CINTILLO --}}
    <div id="header">
        {{-- Asumiendo que tu cintillo está en public/images/cintillo_requisicion.png --}}
        {{-- DOMPDF funciona mejor con rutas absolutas del sistema de archivos para imágenes locales --}}
        @if (file_exists(public_path('img/cintillo_requisicion.png')))
            <img src="{{ public_path('img/cintillo_requisicion.png') }}" alt="Cintillo Institucional">
        @else
            <p style="color:red; text-align:center;">Error: Imagen del cintillo no encontrada en
                public/img/cintillo_requisicion.png</p>
        @endif
    </div>

    {{-- PIE DE PÁGINA --}}
    <div id="footer">
        <p style="text-align: center">
            “El Sol de Venezuela nace en el Esequibo”
            <hr style="color: red">
            Nº 28, Edificio UPTBolívar, Calle Igualdad, entre Rosario y Progreso, Casco Histórico <br>
            Ciudad Bolívar, Municipio Angostura del Orinoco, Estado Bolívar <br>
            Teléfono (0285) 6320221 | Email: XXXXXXXXXXXXX

        </p>
        {{-- <p class="page-number"></p> Para el número de página --}}
    </div>


    {{-- Lógica para determinar el texto de la marca de agua --}}
    @php
        $watermarkText = null;
        if ($requisicion->estado === 'Borrador') {
            $watermarkText = 'BORRADOR';
        } elseif ($requisicion->estado === 'Anulada') {
            $watermarkText = 'ANULADA';
        }
        $nombre_moneda = $nombre_moneda ?? 'Bs.'; // Moneda por defecto
        $iva_porcentaje = $iva_porcentaje ?? 0.16; // 16% IVA por defecto

        // Cálculos de Totales
        $subtotalItems = 0;
        foreach ($items as $item) {
            if ($item->precio_unitario) {
                // Solo sumar si hay precio unitario
                $subtotalItems += $item->cantidad * $item->precio_unitario;
            }
        }
        $montoIva = $subtotalItems * $iva_porcentaje;
        $totalGeneral = $subtotalItems + $montoIva;
    @endphp

    @if ($watermarkText)
        <div class="watermark-container">
            <div class="watermark-text">{{ $watermarkText }}</div>
        </div>
    @endif

    <main class="content">
        <div class="bold text-center" style="font-size: 12pt; margin-bottom: 5px;">REQUISICIÓN</div>
        {{-- ENCABEZADO DEL DOCUMENTO --}}
        <table border="1" cellspacing="0" ceelpadding="0">
            <tr>
                <th style="width: 31%">1.UNIDAD SOLICITANTE</th>
                <th style="border-bottom: 1px solid transparent;">4.TIPO</th>
                <th style="width: 29%">5. FECHA</th>
            </tr>
            <tr>
                <table border="1" style="width: 100%;" cellspacing="0">
                    <tr>
                        <td align="center">{{ $requisicion->dependencia_solicitante }}</td>
                    </tr>
                    <tr>
                        <td class="field-label">2. RESPONSABLE DE PROYECTO:</td>
                    </tr>
                    <tr>
                        <td align="center">{{ $proyectoAccionResponsable ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="field-label">3. CÓDIGO DE PROYECTO/ACCIÓN ESPECÍFICA:</td>
                    </tr>
                    <tr>
                        <td align="center"> {{ $requisicion->proyecto_accion_codigo ?? 'N/A' }}</td>
                    </tr>
                </table>
                <td style="padding: 10px;">
                    <div style="margin-bottom: 10px;">
                        <span class="checkbox-label">
                            <span
                                class="checkbox">{{ $requisicion->tipo_requisicion == 'MATERIALES Y SUMINISTROS' ? 'X' : ' ' }}</span>
                            MATERIALES Y SUMINISTROS
                        </span>
                        <br><br>
                        <span class="checkbox-label">
                            <span class="checkbox">{{ $requisicion->tipo_requisicion == 'SERVICIO' ? 'X' : ' ' }}</span>
                            SERVICIO
                        </span>
                        <br><br>
                        <span class="checkbox-label">
                            <span class="checkbox">{{ $requisicion->tipo_requisicion == 'BIENES' ? 'X' : ' ' }}</span>
                            BIENES
                        </span>
                    </div>
                </td>
                <table border="0" style="width: 100%;" cellspacing="0">
                    <tr>
                        <td class="field-label" style="border-right: 1px solid #000;">DÍA:</td>
                        <td class="field-label" style="border-right: 1px solid #000;">MES:</td>
                        <td class="field-label">AÑO:</td>
                    </tr>

                    <tr>
                        <td align="center" style="border-right: 1px solid #000; width: 30%;">
                            {{ $requisicion->fecha_solicitud->format('d') }}</td>
                        <td align="center" style="border-right: 1px solid #000; width: 30%;">
                            {{ $requisicion->fecha_solicitud->format('m') }}</td>
                        <td align="center">
                            {{ $requisicion->fecha_solicitud->format('Y') }}</td>
                    </tr>
                </table>
                <hr>
                <table border="0" style="width: 100%;" cellspacing="0">

                    <tr>
                        <td align="center" class="field-label">6. NÚMERO:</td>
                    </tr>
                    <tr>
                        <td align="center" valign="bottom">
                            <br>
                            {{ $requisicion->numero_requisicion }}
                        </td>
                        </td>
                    </tr>
                </table>
            </tr>


        </table>

        {{-- TABLA DE ÍTEMS --}}
        <table class="item-table" border="1">
            <thead>
                <tr>
                    <th style="width:5%;">7. RENG.</th>
                    <th style="width:10%;">8. CANTIDAD</th>
                    <th style="width:15%;">9. UNIDAD DE MEDIDA</th>
                    <th style="width:40%;">10. DESCRIPCIÓN</th>
                    <th style="width:15%;">11. PRECIO UNITARIO ({{ $nombre_moneda }})</th>
                    <th style="width:15%;">12. PRECIO ESTIMADO ({{ $nombre_moneda }})</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">{{ number_format($item->cantidad, 2, ',', '.') }}</td>
                        <td class="text-center">{{ $item->unidad_medida }}</td>
                        <td>{{ $item->descripcion }}</td>
                        <td class="text-right">
                            {{ $item->precio_unitario !== null ? number_format($item->precio_unitario, 2, ',', '.') : '-' }}
                        </td>
                        <td class="text-right">
                            {{ $item->precio_unitario !== null ? number_format($item->cantidad * $item->precio_unitario, 2, ',', '.') : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No hay ítems solicitados.</td>
                    </tr>
                @endforelse
                {{-- Filas vacías para completar hasta un mínimo si es necesario, o quitar esto --}}
                @for ($i = count($items); $i < 10; $i++)
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                @endfor
            </tbody>
        </table>

        {{-- TOTALES --}}
        <table
            style="width: 40%; margin-left: 60%; margin-top: -1px; /* Para que se alinee con la tabla de items sin espacio de borde */">
            <tr>
                <td class="field-label text-right" style="width: 70%;">13. SUBTOTAL ({{ $nombre_moneda }}):</td>
                <td class="text-right" style="width: 30%;">{{ number_format($subtotalItems, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="field-label text-right">14. I.V.A. ({{ $iva_porcentaje * 100 }}%) ({{ $nombre_moneda }}):
                </td>
                <td class="text-right">{{ number_format($montoIva, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="field-label text-right">15. TOTAL ({{ $nombre_moneda }}):</td>
                <td class="text-right bold">{{ number_format($totalGeneral, 2, ',', '.') }}</td>
            </tr>
        </table>

        {{-- OBSERVACIONES --}}
        <table border="1">
            <tr>
                <th align="left">16. OBSERVACIÓN:</th>
            </tr>
            <tr>
                <td style="height: 80px;">
                    {{ $requisicion->observaciones ?? 'Ninguna.' }}
                </td>
            </tr>
        </table>


        {{-- FUENTE DE FINANCIAMIENTO --}}

        <table border="1" cellpadding="0">
            <tr>
                <th style="width: 40%">17. FUENTE DE FINANCIAMIENTO:</th>
                <th>18. AÑO PRESUPESTARIO</th>
                <th style="width: 35%">
                    <small> PARA SER LLENADO POR PRESUPUESTO:</small>
                    <br>
                    19.CODIFICACIÓN PRESUPESTARIA
                </th>
            </tr>
            <tr>
                <td>
                    SALDO INICAL DE CAJA (S.I.C.) <span class="checkbox"
                        style="float: right; @if ($requisicion->fuente_financiamiento_nombre == 'SALDO INICAL DE CAJA') background-color:#000; @endif"></span>
                    <br>
                    <hr>
                    PRESUPUESTO LEY (PPTO. LEY) <span class="checkbox"
                        style="float: right; @if ($requisicion->fuente_financiamiento_nombre == 'PRESUPUESTO LEY') background-color:#000; @endif"></span>
                    <br>
                    <hr>
                    CRÉDITO ADICIONAL (C.A.) <span class="checkbox"
                        style="float: right; @if ($requisicion->fuente_financiamiento_nombre == 'CRÉDITO ADICIONAL') background-color:#000; @endif"></span>
                </td>
                <td valign="middle">
                    <br>
                    <center>
                        <h3>

                            {{ $requisicion->Periodo->anio }}
                        </h3>
                    </center>
                </td>
                <td>

                </td>
            </tr>
        </table>

        <table border="1">
            <tr>
                <th>20. ELABORADO POR</th>
                <th>21.VERIFICADO POR</th>
                <th>22.VALIDADO POR</th>
                <th>23.RECIBIDO POR</th>
            </tr>
            <tr>
                <td align="center" valign="bottom" style="width:25%; height: 100px;">EJECUTOR-UNIDAD SOLICITANTE</td>
                <td align="center" valign="bottom" style="width:25%; height: 100px;">RESPONSABLE DE PROYECTO</td>
                <td align="center" valign="bottom" style="width:25%; height: 100px;">RESPONSABLE DE PRESUPUESTO</td>
                <td align="center" valign="bottom" style="width:25%; height: 100px;">RESPONSABLE DE GESTIÓN
                    ADMINISTRATIVA</td>
            </tr>
        </table>

        <table>
            <tr>
                <th>FIRMA Y SELLO</th>
                <th>FIRMA Y SELLO</th>
            </tr>
        </table>

    </main>

</body>

</html>
