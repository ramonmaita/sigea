<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Comunicación N° {{ $comunicacion->numero_comunicacion }}</title>
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
            font-family: 'Helvetica', Arial, sans-serif; /* O 'DejaVu Sans' */
            font-size: 12pt; /* Un poco más grande que otros formatos */
            line-height: 1.4;
            color: #000;
            position: relative;
        }
        .header-top {
            text-align: center;
            font-size: 9pt;
            line-height: 1.1;
            margin-bottom: 10px;
        }
        .header-top .main-institution { font-size: 12pt; font-weight: bold; }

        .document-identifier {
            text-align: right;
            font-size: 12pt;
            margin-bottom: 15px;
        }
        .document-identifier div { margin-bottom: 2px; }

        .address-block {
            margin-bottom: 15px;
            font-size: 12pt;
            line-height: 1.3;
        }
        .address-block .label { font-weight: bold; }

        .subject-block {
            margin-bottom: 15px;
            font-size: 12pt;
        }
        .subject-block .label { font-weight: bold; }

        .document-body {
            text-align: justify;
            font-size: 11pt; /* Igual al body general */
            line-height: 1.5; /* Espaciado para el cuerpo */
            margin-bottom: 20px;
        }
        .document-body table { /* Estilos para tablas dentro del RichEditor */
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 10px;
        }
        .document-body th, .document-body td {
            border: 1px solid #333;
            padding: 4px 6px;
            font-size: 9pt; /* Tablas internas un poco más pequeñas */
        }
        .document-body th {
            background-color: #e8e8e8;
            text-align: center;
            font-weight: bold;
        }


        .closing-block {
            margin-top: 30px;
            font-size: 12pt;
            text-align: center;
        }

        .signature-block {
            margin-top: 50px; /* Menos espacio si "Atentamente," está cerca */
            text-align: center;
            page-break-inside: avoid;

        }
        .signature-block .firmante-nombre { font-weight: bold; }
        .signature-block .firmante-cargo { font-size: 12pt; }
        .signature-block .firmante-referencias { font-size: 8pt; line-height: 1.1; }

        .initials-block {
            margin-top: 40px;
            font-size: 8pt;
            text-align: left;
        }

        /* Marca de Agua */
        .watermark-container { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1000; }
        .watermark-text {
            position: absolute; top: 45%; left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 72pt; color: rgba(0, 0, 0, 0.07);
            font-weight: bold; text-transform: uppercase; white-space: nowrap;
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
            text-align: right;
            content: "Página " counter(page);
            /* Para numeración de página */
        }

        .field-label {
            font-weight: bold;
            /* Para resaltar las etiquetas de los campos */
        }
    </style>
</head>
<body>
    @php
        $watermarkText = null;
        if (in_array($comunicacion->estado, ['Borrador', 'Anulada'])) {
            $watermarkText = $comunicacion->estado;
            if ($comunicacion->estado === 'Borrador') $watermarkText = 'BORRADOR';
            if ($comunicacion->estado === 'Anulada') $watermarkText = 'ANULADA';
        }
        $ciudad_actual = "Ciudad Bolívar"; // Puedes obtenerlo de DetalleOficina si lo tienes
    @endphp

    @if($watermarkText)
    <div class="watermark-container">
        <div class="watermark-text">{{ $watermarkText }}</div>
    </div>
    @endif

    {{-- CABECERA / CINTILLO --}}
    <div id="header" >
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
        <p class="page-number"></p>
    </div>


    <div class="document-identifier">
        <div><span class="field-label">N°</span> {{ $comunicacion->numero_comunicacion }}</div>
        <div>{{ $ciudad_actual }}, {{ $comunicacion->fecha_documento->format('d') }} de {{ $comunicacion->fecha_documento->getTranslatedMonthName('F') }} de {{ $comunicacion->fecha_documento->format('Y') }}</div>
    </div>

    <div class="address-block">
        <div><span class="field-label">Para:</span> {{ $comunicacion->dirigido_a_nombre }}</div>
        @if($comunicacion->dirigido_a_cargo_dependencia)
        <div><span class="field-label">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> {{ $comunicacion->dirigido_a_cargo_dependencia }}</div>
        @endif
    </div>

    @if($comunicacion->con_copia_a && count($comunicacion->con_copia_a) > 0)
        <div class="address-block">
            <span class="field-label">Cc:</span><br>
            @foreach($comunicacion->con_copia_a as $cc)
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $cc['destinatario_cc'] ?? '' }}<br> {{-- Asumiendo que guardaste 'destinatario_cc' en el Repeater --}}
            @endforeach
        </div>
    @endif

    <div class="address-block">
        <div><span class="field-label">De:</span> {{ $comunicacion->firmante_nombre }}</div>
        <div><span class="field-label">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> {{ $comunicacion->firmante_cargo }}</div>
    </div>

    <div class="subject-block">
        <span class="field-label">Asunto:</span> {{ $comunicacion->asunto }}
    </div>

    @if($comunicacion->referencia)
        <div class="subject-block">
            <span class="field-label">Referencia:</span> {{ $comunicacion->referencia }}
        </div>
    @endif

    <div class="document-body">
        Reciba un cordial y fraternal saludo Bolivariano.
        <br>
        {!! $comunicacion->cuerpo !!} {{-- Renderizar el HTML del RichEditor --}}
    </div>

    <div class="closing-block">
        Atentamente,
    </div>

    <div class="signature-block">
        <div class="firmante-nombre">{{ $comunicacion->firmante_nombre }}</div>
        <div class="firmante-cargo">{{ $comunicacion->firmante_cargo }}</div>
        {{-- Si tuvieras un campo para las referencias adicionales del firmante: --}}
        {{-- <div class="firmante-referencias">{{ $comunicacion->firmante_referencias_adicionales }}</div> --}}
    </div>

    <div class="initials-block">
        @if($comunicacion->creador) {{-- Asumiendo relación 'creador' con User --}}
            {{-- Generar iniciales, ej: tomar primeras letras de nombre y apellido --}}
            @php
                $nombres = explode(' ', $comunicacion->creador->nombre);
                $apellidos = explode(' ', $comunicacion->creador->apellido);
                $iniciales = '';
                if (count($nombres) > 0) $iniciales .= strtoupper(substr($nombres[0], 0, 1));
                if (count($apellidos) > 0) $iniciales .= strtoupper(substr($apellidos[0], 0, 1));
                // if (count($nombres) > 1) $iniciales .= strtoupper(substr(end($apellidos), 0, 1)); // Inicial del último apellido
            @endphp
            {{ $iniciales }}/SIGEA
        @endif
    </div>

</body>
</html>
