<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Orden de Salida N° {{ $ordenSalida->numero_orden_salida }}</title>
    <style>
        @page {
            margin-top: 100px; /* Espacio para el cintillo/cabecera fija */
            margin-bottom: 60px; /* Espacio para el pie de página fijo */
            margin-left: 40px;
            margin-right: 40px;
        }
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.3;
            color: #000;
            position: relative;
        }
        #header {
            position: fixed;
            top: -85px; /* Ajusta según el alto de tu cintillo/logo */
            left: 0px;
            right: 0px;
            width: 100%;
            text-align: center;
        }
        #header img {
            max-width: 100%;
            max-height: 75px; /* Ajusta */
        }
        #footer {
            position: fixed;
            bottom: -45px; /* Ajusta */
            left: 0px;
            right: 0px;
            width: 100%;
            text-align: center;
            font-size: 8pt;
        }
        #footer .page-number:before {
            content: "Página " counter(page);
        }
        .content {
            /* El contenido principal comenzará después del margin-top de @page */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #777;
            padding: 4px 6px;
            vertical-align: top;
        }
        th {
            background-color: #f0f0f0;
            text-align: center;
            font-weight: bold;
        }
        .no-border-table, .no-border-table td {
            border: none;
        }
        .main-title { font-size: 14pt; font-weight: bold; text-align: center; margin-bottom: 20px; }
        .field-label { font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .signature-block { margin-top: 40px; page-break-inside: avoid; }
        .signature-box { display: inline-block; width: 45%; text-align: center; margin-top: 30px; border-top: 1px solid #000; padding-top: 5px; }

        /* Marca de Agua */
        .watermark-container { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1000; }
        .watermark-text {
            position: absolute; top: 45%; left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 72pt; color: rgba(0, 0, 0, 0.08);
            font-weight: bold; text-transform: uppercase; white-space: nowrap;
        }
    </style>
</head>
<body>
    <div id="header">
        @if($detalleOficina && $detalleOficina->path_logo)
            {{-- Asumiendo que tienes una forma de mostrar el cintillo/logo --}}
            {{-- <img src="{{ public_path(Storage::url($detalleOficina->path_logo)) }}" alt="Cintillo/Logo"> --}}
            {{-- O si es un texto: --}}
             <p style="font-size:12pt; font-weight:bold; margin:0;">{{ $detalleOficina->nombre_oficina ?? 'INSTITUCIÓN' }}</p>
             <p style="font-size:8pt; margin:0;">{{ $detalleOficina->rif ?? '' }}</p>
        @else
            <p style="font-size:12pt; font-weight:bold; margin:0;">{{ $nombre_oficina_por_defecto ?? 'NOMBRE DE LA INSTITUCIÓN' }}</p>
        @endif
    </div>

    <div id="footer">
        <p class="page-number"></p>
    </div>

    @php
        $watermarkText = null;
        if ($ordenSalida->estado_orden === 'Anulada') {
            $watermarkText = 'ANULADA';
        }
    @endphp

    @if($watermarkText)
    <div class="watermark-container">
        <div class="watermark-text">{{ $watermarkText }}</div>
    </div>
    @endif

    <main class="content">
        <div class="main-title">ORDEN DE SALIDA DE BIENES</div>

        <table class="no-border-table" style="margin-bottom: 15px;">
            <tr>
                <td style="width:70%;"></td>
                <td style="width:30%; border: 1px solid #000; padding:5px;">
                    <span class="field-label">N° ORDEN:</span> <span style="color:red; font-weight:bold;">{{ $ordenSalida->numero_orden_salida }}</span><br>
                    <span class="field-label">FECHA SALIDA:</span> {{ $ordenSalida->fecha_salida ? $ordenSalida->fecha_salida->format('d/m/Y') : '' }}
                </td>
            </tr>
        </table>

        <p><span class="field-label">TIPO DE SALIDA:</span> {{ $ordenSalida->tipo_salida }}</p>
        <p><span class="field-label">DESTINO / UNIDAD RECEPTORA:</span> {{ $ordenSalida->destino_o_unidad_receptora }}</p>
        <p><span class="field-label">PERSONA RESPONSABLE DEL RETIRO:</span> {{ $ordenSalida->persona_responsable_retiro }}
            @if($ordenSalida->cedula_responsable_retiro)
                (C.I: {{ $ordenSalida->cedula_responsable_retiro }})
            @endif
        </p>

        @if($ordenSalida->tipo_salida === 'Reparación')
            <p class="section-title" style="margin-top:10px;">DATOS DEL PROVEEDOR (REPARACIÓN):</p>
            <p><span class="field-label">Nombre:</span> {{ $ordenSalida->proveedor_nombre ?? 'N/A' }}</p>
            <p><span class="field-label">Dirección:</span> {{ $ordenSalida->proveedor_direccion ?? 'N/A' }}</p>
            <p><span class="field-label">Teléfono:</span> {{ $ordenSalida->proveedor_telefono ?? 'N/A' }}</p>
            {{-- <p><span class="field-label">RIF:</span> {{ $ordenSalida->proveedor_rif ?? 'N/A' }}</p> --}}
        @endif

        @if($ordenSalida->fecha_retorno_prevista)
            <p><span class="field-label">FECHA DE RETORNO PREVISTA:</span> {{ $ordenSalida->fecha_retorno_prevista->format('d/m/Y') }}</p>
        @endif
        @if($ordenSalida->fecha_retorno_real)
            <p><span class="field-label">FECHA DE RETORNO REAL:</span> {{ $ordenSalida->fecha_retorno_real->format('d/m/Y') }}</p>
        @endif

        <p class="section-title" style="margin-top:15px;">JUSTIFICACIÓN:</p>
        <div style="border: 1px solid #000; padding: 5px; min-height: 40px; margin-bottom:10px;">
            {{ $ordenSalida->justificacion }}
        </div>

        <p class="field-label">BIENES INCLUIDOS EN LA ORDEN:</p>
        <table>
            <thead>
                <tr>
                    <th style="width:10%;">Código Bien</th>
                    <th style="width:35%;">Nombre / Descripción del Bien</th>
                    <th style="width:20%;">Serial</th>
                    <th style="width:35%;">Observación Específica del Ítem</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ordenSalida->biens as $bien)
                    <tr>
                        <td class="text-center">{{ $bien->codigo_bien }}</td>
                        <td>{{ $bien->nombre }}</td>
                        <td class="text-center">{{ $bien->serial_numero ?? 'N/A' }}</td>
                        <td>{{ $bien->pivot->observacion_item_salida ?? '' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">No hay bienes asociados a esta orden.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($ordenSalida->observaciones)
            <p class="section-title" style="margin-top:10px;">OBSERVACIONES GENERALES DE LA ORDEN:</p>
            <div style="border: 1px solid #000; padding: 5px; min-height: 30px; margin-bottom:10px;">
                {{ $ordenSalida->observaciones }}
            </div>
        @endif

        <div class="signature-block">
            <table class="no-border-table">
                <tr>
                    <td class="signature-box">
                        ENTREGADO POR:<br/>
                        {{-- Nombre y C.I. de quien entrega (quizás el usuario solicitante o aprobador) --}}
                        {{-- Si tienes user_id_solicitante: $ordenSalida->solicitante->name ?? '' --}}
                    </td>
                    <td style="width:10%; border:none;"></td>
                    <td class="signature-box">
                        RECIBIDO POR:<br/>
                        {{ $ordenSalida->persona_responsable_retiro }}<br/>
                        C.I: {{ $ordenSalida->cedula_responsable_retiro ?? ''}}
                    </td>
                </tr>
                 <tr>
                    <td class="signature-box" style="padding-top: 35px;">
                        APROBADO POR:<br/>
                        {{-- Nombre y C.I. de quien aprueba --}}
                        {{-- Si tienes user_id_aprobador: $ordenSalida->aprobador->name ?? '' --}}
                    </td>
                    <td style="width:10%; border:none;"></td>
                    <td class="signature-box" style="padding-top: 35px;">
                        AUTORIZADO POR: <br/> (Jefe de Bienes Nacionales / Director)
                    </td>
                </tr>
            </table>
        </div>
    </main>
</body>
</html>
