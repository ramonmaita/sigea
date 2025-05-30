<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>SOLICITUD DE TRANSFERENCIA DE BIENES N° {{ $solicitud->numero_solicitud_rea }}</title>
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

        .titulos {
            font-weight: bold;
            font-size: 9pt;
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
        if ($solicitud->estado_solicitud === 'Borrador') {
            $watermarkText = 'BORRADOR';
        } elseif ($solicitud->estado_solicitud === 'Anulada') {
            $watermarkText = 'ANULADA';
        }
        $nombre_moneda = $nombre_moneda ?? 'Bs.'; // Moneda por defecto
        $iva_porcentaje = $iva_porcentaje ?? 0.16; // 16% IVA por defecto

        // Cálculos de Totales
        $subtotalItems = 0;
        $totalGeneral = 0;
        foreach ($solicitud->biens as $bien) {
            $totalGeneral += $bien->valor_adquisicion;
        }
    @endphp

    @if ($watermarkText)
        <div class="watermark-container">
            <div class="watermark-text">{{ $watermarkText }}</div>
        </div>
    @endif

    <main class="content">
        <div class="bold text-center" style="font-size: 12pt; margin-bottom: 5px;">SOLICITUD DE TRANSFERENCIA DE BIENES

        </div>
        <br>
        {{-- ENCABEZADO DEL DOCUMENTO --}}
        <table border="1" cellspacing="0" ceelpadding="0">
            <tr>
                <td colspan="2" style="width: 31%"><span class="titulos">1.UNIDAD SOLICITANTE:</span><br>
                    {{ $solicitud->unidad_administrativa_destino   }}</td>
                <td><span class="titulos">2.Nº.:</span><br> {{ $solicitud->numero_solicitud_rea }}</td>
            </tr>
            <tr>
                <td style="width: 50%"><span class="titulos">3.NOMBRE Y APELLIDO:</span><br> {{ $solicitud->responsable_destino   }}
                </td>
                <td><span class="titulos">C.I:</span><br> {{ $solicitud->cedula_responsable_destino   }}</td>
                <td><span class="titulos">4. FECHA:</span><br> {{ $solicitud->fecha_ejecucion_rea ? $solicitud->fecha_ejecucion_rea->format('d/m/Y') : ($solicitud->fecha_aprobacion ? $solicitud->fecha_aprobacion->format('d/m/Y') : $solicitud->fecha_solicitud->format('d/m/Y')) }}</td>
            </tr>
            <tr>
                <td colspan="3" style="height: 50px;" valign="top">
                    <span class="titulos">5. MOTIVO DE LA REASIGNACIÓN:</span> {{ $solicitud->motivo_reasignacion  }}
                </td>
            </tr>
            <tr>
                <th colspan="3">

                </th>
            </tr>
            <tr>
                <td colspan="3">
                    <span class="titulos">6. UNIDAD RESPONSABLE PATRIMONIAL DE USO (CEDENTE): </span>
                    {{ $solicitud->unidad_administrativa_origen  ?? 'N/A' }}
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="titulos">7. APELLIDOS Y NOMBRES:</span> {{ $solicitud->responsable_actual_origen ?? 'N/A' }}
                </td>
                <td>
                    <span class="titulos">C.I. Nº:</span> {{ $solicitud->cedula_responsable_origen ?? 'N/A' }}
                </td>
            </tr>
        </table>
        <br><br>

        {{-- TABLA DE ÍTEMS --}}
        <table class="item-table" border="1">
            <thead>
                <tr>
                    <th style="width:5%;">8. ITEM</th>
                    <th style="width:10%;">9. NÚMERO DE INVENTARIO</th>
                    <th style="width:15%;">10. SERIALES</th>
                    <th style="width:40%;">11. DESCRIPCIÓN</th>
                    <th style="width:15%;">12. VALOR</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($solicitud->biens as $index => $bien)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">{{ $bien->codigo_bien ?? 'N/A' }}</td>
                        <td class="text-center">{{ $bien->serial_numero ?? 'N/A' }}</td>
                        <td>{{ $bien->nombre }}</td>
                        <td class="text-right">
                            {{ $bien->valor_adquisicion !== null ? number_format($bien->valor_adquisicion, 2, ',', '.') : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No hay ítems solicitados.</td>
                    </tr>
                @endforelse
                {{-- Filas vacías para completar hasta un mínimo si es necesario, o quitar esto --}}
                @for ($i = count($solicitud->biens); $i < 20; $i++)
                    <tr>
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
                <td class="field-label text-right">13. TOTAL ({{ $nombre_moneda }}):</td>
                <td class="text-right bold">{{ number_format($totalGeneral, 2, ',', '.') }}</td>
            </tr>
        </table>

        {{-- FIRMAS --}}

        <table border="1">

            <tr>
                <th align="center" valign="top" style="width:25%; height: 100px;">14. UNIDAD SOLICITANTE</th>
                <th align="center" valign="top" style="width:25%; height: 100px;">15. UNIDAD SOLICITANTE</th>
                <th align="center" valign="top" style="width:25%; height: 100px;">16.:BIENES PÚBLICOS</th>
            </tr>
        </table>

    </main>

</body>

</html>
