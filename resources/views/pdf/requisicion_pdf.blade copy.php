<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-G">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Requisición N° {{ $requisicion->numero_requisicion }}</title>
    <style>
        /* Estilos CSS básicos aquí */
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            margin: 20px;
        }

        .header,
        .footer {
            width: 100%;
            text-align: center;
            position: fixed;
        }

        .header {
            top: 0px;
        }

        .footer {
            bottom: 0px;
            font-size: 8px;
        }

        .content {
            margin-top: 100px;
            /* Ajusta según el tamaño de tu cabecera */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 5px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .logo {
            max-width: 150px;
            max-height: 70px;
            margin-bottom: 10px;
        }

        /* Más estilos según tu formato */
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            margin: 20px;
            position: relative;
            /* Necesario para que el z-index de la marca de agua funcione bien */
        }

        /* Estilos para la Marca de Agua */
        .watermark-container {
            position: fixed;
            /* Fijo respecto al viewport del PDF */
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            /* Para que esté detrás del contenido */
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            /* Para que no se desborde */
        }

        .watermark-text {
            font-size: 80pt;
            /* Tamaño grande para la marca de agua, ajusta según necesites */
            color: rgba(128, 128, 128, 0.15);
            /* Color gris muy claro y semi-transparente */
            font-weight: bold;
            text-transform: uppercase;
            transform: rotate(-45deg);
            /* Rotar el texto */
            white-space: nowrap;
            /* Evitar que el texto se parta */
            /* Puedes necesitar ajustar estos valores para centrar mejor el texto rotado */
            padding: 50px;
        }
    </style>
</head>

<body>
    {{-- Lógica para determinar el texto de la marca de agua --}}
    @php
        $watermarkText = null;
        if (in_array($requisicion->estado, ['Borrador', 'Anulada'])) {
            $watermarkText = $requisicion->estado; // O un texto personalizado: 'BORRADOR', 'ANULADA'
        }
        // Si quieres textos específicos en mayúsculas:
        if ($requisicion->estado === 'Borrador') {
            $watermarkText = 'BORRADOR';
        } elseif ($requisicion->estado === 'Anulada') {
            $watermarkText = 'ANULADA';
        }
    @endphp

    {{-- Contenedor de la Marca de Agua --}}
    @if ($watermarkText)
        <div class="watermark-container">
            <div class="watermark-text">
                {{ $watermarkText }}
            </div>
        </div>
    @endif
    {{-- Puedes definir un header fijo si DOMPDF lo soporta bien con tu diseño --}}
    {{-- <div class="header">
        @if ($detalleOficina && $detalleOficina->path_logo)
            <img src="{{ public_path(Storage::url($detalleOficina->path_logo)) }}" alt="Logo" class="logo">
        @endif
        <h2>{{ $detalleOficina->nombre_oficina ?? 'Nombre de la Institución' }}</h2>
        <p>{{ $detalleOficina->rif ?? 'RIF de la Institución' }}</p>
        <h3>REQUISICIÓN DE MATERIALES, EQUIPOS O SERVICIOS</h3>
    </div> --}}

    {{-- Contenido Principal --}}
    <div class="content">
        <table style="border: none; margin-bottom: 20px;">
            <tr>
                <td style="border: none; width: 70%;">
                    @if ($detalleOficina && $detalleOficina->path_logo)
                        {{-- Considera el tamaño y cómo se verá el logo --}}
                        {{-- <img src="{{ public_path(Storage::url($detalleOficina->path_logo)) }}" alt="Logo" style="max-height: 60px;"> --}}
                        {{-- Alternativamente, si el logo es una imagen pública accesible por URL directa: --}}
                        {{-- <img src="{{ asset(Storage::url($detalleOficina->path_logo)) }}" alt="Logo" style="max-height: 60px;"> --}}
                        {{-- Si la ruta es absoluta, DOMPDF podría necesitarla --}}
                    @endif
                    <span class="bold"
                        style="font-size: 12px;">{{ $detalleOficina->nombre_oficina ?? 'Nombre de la Institución' }}</span><br>
                    {{ $detalleOficina->direccion ?? 'Dirección' }}<br>
                    RIF: {{ $detalleOficina->rif ?? 'RIF' }} :: Teléfonos:
                    {{ $detalleOficina->telefonos ?? 'Teléfonos' }}
                </td>
                <td style="border: 1px solid #000; width: 30%; text-align:center;">
                    <span class="bold">REQUISICIÓN N°:</span><br>
                    <span style="color: red; font-size: 14px;">{{ $requisicion->numero_requisicion }}</span>
                </td>
            </tr>
        </table>

        <p><span class="bold">FECHA DE SOLICITUD:</span> {{ $requisicion->fecha_solicitud->format('d/m/Y') }}</p>
        <p><span class="bold">DEPENDENCIA SOLICITANTE:</span> {{ $requisicion->dependencia_solicitante }}</p>
        <p><span class="bold">TIPO DE REQUISICIÓN:</span> {{ $requisicion->tipo_requisicion }}</p>
        <p><span class="bold">JUSTIFICACIÓN DEL USO O DESTINO:</span> {{ $requisicion->justificacion_uso }}</p>

        @if ($requisicion->proyecto_accion_codigo)
            <p><span class="bold">PROYECTO/ACCIÓN:</span> {{ $requisicion->proyecto_accion_codigo }} -
                {{ $proyectoAccionNombre ?? 'N/A' }}</p>
            <p><span class="bold">RESPONSABLE PROYECTO/ACCIÓN:</span> {{ $proyectoAccionResponsable ?? 'N/A' }}</p>
        @endif
        @if ($requisicion->fuente_financiamiento_nombre)
            <p><span class="bold">FUENTE DE FINANCIAMIENTO:</span> {{ $requisicion->fuente_financiamiento_nombre }}
            </p>
        @endif

        <p class="bold" style="margin-top: 15px;">ÍTEMS SOLICITADOS:</p>
        <table>
            <thead>
                <tr>
                    <th class="text-center">Ítem</th>
                    <th class="text-center">Cantidad</th>
                    <th>Unidad de Medida</th>
                    <th>Descripción del Artículo o Servicio Solicitado</th>
                    <th class="text-right">Precio Unit. (Ref.)</th>
                    <th class="text-right">Total (Ref.)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">{{ number_format($item->cantidad, 2, ',', '.') }}</td>
                        <td>{{ $item->unidad_medida }}</td>
                        <td>{{ $item->descripcion }}</td>
                        <td class="text-right">
                            {{ $item->precio_unitario ? number_format($item->precio_unitario, 2, ',', '.') : '-' }}
                        </td>
                        <td class="text-right">
                            {{ $item->precio_unitario ? number_format($item->cantidad * $item->precio_unitario, 2, ',', '.') : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No hay ítems en esta requisición.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($requisicion->observaciones)
            <p><span class="bold">OBSERVACIONES:</span></p>
            <p>{{ $requisicion->observaciones }}</p>
        @endif

        {{-- Espacio para Firmas (simplificado, ajusta según tu formato) --}}
        <table style="margin-top: 50px; border: none;">
            <tr style="text-align: center;">
                <td style="border: none; width: 33%; padding-top: 40px; border-top: 1px solid #000;">
                    Responsable Unidad Solicitante <br>
                    ({{ $requisicion->user->name ?? 'Nombre Solicitante' }})
                </td>
                <td style="border: none; width: 33%;"></td>
                <td style="border: none; width: 33%; padding-top: 40px; border-top: 1px solid #000;">
                    P.P.U. / Presupuesto
                </td>
            </tr>
            <tr style="text-align: center; page-break-inside: avoid;">
                <td style="border: none; width: 33%; padding-top: 60px; border-top: 1px solid #000;">
                    Compras
                </td>
                <td style="border: none; width: 33%;"></td>
                <td style="border: none; width: 33%; padding-top: 60px; border-top: 1px solid #000;">
                    Administración / Director
                </td>
            </tr>
        </table>
    </div>

    {{-- <div class="footer">
        SIGEA - Sistema Integrado de Gestión Administrativa - {{ date('Y') }}
    </div> --}}
</body>

</html>
