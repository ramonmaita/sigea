<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Acta de Desincorporación N° {{ $solicitud->numero_solicitud_des }}</title>
    <style>
        @page {
            margin: 40px 50px; /* Margen general */
        }
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #000;
        }
        .header-text { text-align: center; line-height: 1.2; margin-bottom: 20px; }
        .header-text .main-institution { font-size: 12pt; font-weight: bold; }
        .header-text .sub-institution { font-size: 10pt; }
        .document-title { font-size: 14pt; font-weight: bold; text-align: center; margin-top: 20px; margin-bottom: 20px; text-decoration: underline;}
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #333; padding: 4px 5px; vertical-align: top; }
        th { background-color: #e8e8e8; text-align: center; font-weight: bold; font-size: 9pt; }
        td { font-size: 9pt; }
        .field-label { font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .paragraph { text-align: justify; margin-bottom: 10px; }
        .signature-section { margin-top: 60px; page-break-inside: avoid; }
        .signature-line { border-top: 1px solid #000; width: 250px; margin: 40px auto 5px auto; text-align: center; }
        .signature-title { text-align: center; font-size: 9pt; }

        /* Marca de Agua */
        .watermark-container { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1000; }
        .watermark-text {
            position: absolute; top: 45%; left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 72pt; color: rgba(0, 0, 0, 0.07); /* Más tenue */
            font-weight: bold; text-transform: uppercase; white-space: nowrap;
        }
    </style>
</head>
<body>
    {{-- Lógica para determinar el texto de la marca de agua --}}
    @php
        $watermarkText = null;
        if ($solicitud->estado_solicitud === 'Anulada') { // Solo si está anulada
            $watermarkText = 'ANULADA';
        }
    @endphp

    @if($watermarkText)
    <div class="watermark-container">
        <div class="watermark-text">{{ $watermarkText }}</div>
    </div>
    @endif

    <header class="header-text">
        {{-- Podrías poner el cintillo/logo aquí si es diferente al de la Orden de Salida, o usar un header fijo --}}
        <div class="main-institution">{{ $detalleOficina->nombre_oficina ?? 'NOMBRE DE LA INSTITUCIÓN' }}</div>
        <div class="sub-institution">RIF: {{ $detalleOficina->rif ?? 'J-00000000-0' }}</div>
        {{-- <div class="sub-institution">DIRECCIÓN: {{ $detalleOficina->direccion ?? 'Dirección de la Institución' }}</div> --}}
    </header>

    <div class="document-title">ACTA DE DESINCORPORACIÓN DE BIENES MUEBLES</div>

    <p class="paragraph">
        En la ciudad de {{ $ciudad_documento ?? 'Ciudad Bolívar' }}, a los {{ $solicitud->fecha_ejecucion_des ? $solicitud->fecha_ejecucion_des->day : '__' }} días del mes de
        {{ $solicitud->fecha_ejecucion_des ? $solicitud->fecha_ejecucion_des->getTranslatedMonthName('মাস') : '________' }} {{-- getTranslatedMonthName requiere Carbon >= 2.63 y locale configurado --}}
        {{-- Alternativa más simple para el mes: date('F', strtotime($solicitud->fecha_ejecucion_des)) o un array de meses en español --}}
        del año {{ $solicitud->fecha_ejecucion_des ? $solicitud->fecha_ejecucion_des->year : '____' }}, se procede a levantar la presente Acta de Desincorporación
        N° <span class="field-label">{{ $solicitud->numero_solicitud_des }}</span>, correspondiente a la solicitud de fecha
        {{ $solicitud->fecha_solicitud ? $solicitud->fecha_solicitud->format('d/m/Y') : '' }}.
    </p>
    <p class="paragraph">
        <span class="field-label">Unidad Solicitante:</span> {{-- Aquí iría la dependencia que solicitó, si la guardaste --}}
        {{-- $solicitud->solicitante->dependencia ?? $detalleOficina->nombre_oficina ?? 'Unidad Solicitante no especificada' --}}
        <br>
        <span class="field-label">Motivo de la Desincorporación:</span> {{ $solicitud->tipo_motivo_desincorporacion }}.<br>
        <span class="field-label">Justificación Detallada:</span> {{ $solicitud->justificacion_detallada }}.
    </p>
    @if($solicitud->referencia_informe_tecnico)
        <p class="paragraph"><span class="field-label">Referencia Informe Técnico:</span> {{ $solicitud->referencia_informe_tecnico }}</p>
    @endif

    <p class="section-title">BIENES MUEBLES A DESINCORPORAR:</p>
    <table>
        <thead>
            <tr>
                <th style="width:5%;">N°</th>
                <th style="width:15%;">Código del Bien</th>
                <th>Denominación del Bien</th>
                {{-- <th>Marca</th> --}}
                {{-- <th>Modelo</th> --}}
                <th style="width:15%;">Serial</th>
                <th style="width:15%;">Valor Histórico</th>
                {{-- <th style="width:15%;">Estado Actual</th> --}}
                <th style="width:20%;">Observación Específica</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($solicitud->biens as $index => $bien)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $bien->codigo_bien }}</td>
                    <td>{{ $bien->nombre }}</td>
                    {{-- <td>{{ $bien->marca ?? 'N/A' }}</td> --}}
                    {{-- <td>{{ $bien->modelo ?? 'N/A' }}</td> --}}
                    <td class="text-center">{{ $bien->serial_numero ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($bien->valor_adquisicion ?? 0, 2, ',', '.') }}</td>
                    {{-- <td>{{ $bien->estado_bien }}</td> --}} {{-- Este sería el estado ANTES de desincorporar --}}
                    <td>{{ $bien->pivot->observacion_especifica_bien ?? '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No hay bienes detallados en esta solicitud.</td> {{-- Ajusta el colspan si añades Marca/Modelo --}}
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($solicitud->observaciones_generales)
        <p class="paragraph"><span class="field-label">Observaciones Generales Adicionales:</span> {{ $solicitud->observaciones_generales }}</p>
    @endif

    <p class="paragraph" style="margin-top: 20px;">
        En virtud de lo anteriormente expuesto, los bienes detallados quedan formalmente desincorporados del inventario de esta institución.
        Se levanta la presente acta en la fecha y lugar arriba indicados.
    </p>

    <div class="signature-section">
        {{-- Aquí necesitas definir quiénes firman un acta de desincorporación en tu institución --}}
        {{-- Ejemplo: --}}
        <table class="no-border-table" style="width:100%;">
            <tr>
                <td style="width:45%; text-align:center;">
                    <div class="signature-line">&nbsp;</div>
                    <div class="signature-title">
                        {{-- $solicitud->solicitante->name ?? 'Nombre Solicitante' --}} <br>
                        Responsable de la Unidad Solicitante / Jefe de Bienes
                    </div>
                </td>
                <td style="width:10%;">&nbsp;</td>
                <td style="width:45%; text-align:center;">
                    <div class="signature-line">&nbsp;</div>
                    <div class="signature-title">
                        {{-- $solicitud->aprobador->name ?? 'Nombre Autoridad' --}} <br>
                        Autoridad Competente que Aprueba/Ejecuta
                    </div>
                </td>
            </tr>
            {{-- Puedes añadir más firmantes si es necesario --}}
        </table>
    </div>
</body>
</html>
