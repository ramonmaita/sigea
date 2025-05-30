<?php

namespace App\Http\Controllers;

use App\Models\Comunicacion;
use App\Models\DetalleOficina;
use App\Models\OrdenSalida;
use App\Models\Requisicion;
use App\Models\SolicitudDesincorporacion;
use App\Models\SolicitudReasignacion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    // En tu PdfController.php
    public function generarRequisicionPdf(Requisicion $requisicion)
    {
        $requisicion->load('items', 'user', 'periodo');
        $detalleOficina = DetalleOficina::first();
        // ... (lógica para obtener $proyectoAccionNombre y $proyectoAccionResponsable) ...
        // (la que ya tenías, asegúrate que funciona)
        $proyectoAccionNombre = 'Nombre Proyecto Ejemplo'; // Placeholder
        $proyectoAccionResponsable = 'Responsable Ejemplo'; // Placeholder
        if ($requisicion->proyecto_accion_codigo && $detalleOficina && !empty($detalleOficina->proyectos_acciones)) {
            foreach ($detalleOficina->proyectos_acciones as $pa) {
                if (isset($pa['codigo']) && $pa['codigo'] === $requisicion->proyecto_accion_codigo) {
                    $proyectoAccionNombre = $pa['nombre'] ?? 'N/A';
                    $proyectoAccionResponsable = $pa['responsable'] ?? 'N/A';
                    break;
                }
            }
        }

        $data = [
            'requisicion' => $requisicion,
            'items' => $requisicion->items,
            'detalleOficina' => $detalleOficina,
            'proyectoAccionNombre' => $proyectoAccionNombre,
            'proyectoAccionResponsable' => $proyectoAccionResponsable,
            'iva_porcentaje' => 0.16, // Por ejemplo, 16%
            'nombre_moneda' => 'Bs.', // Por ejemplo
        ];

        $pdf = Pdf::loadView('pdf.requisicion_pdf', $data);
        $pdf->setPaper('letter', 'portrait');
        return $pdf->stream('requisicion-' . $requisicion->numero_requisicion . '.pdf');
    }

    public function generarOrdenSalidaPdf(OrdenSalida $ordenSalida)
    {
        $ordenSalida->load('biens', 'periodo'); // Cargar relaciones necesarias
        // Carga 'solicitante', 'aprobador' si los tienes

        $detalleOficina = DetalleOficina::first();
        $nombre_oficina_por_defecto = config('app.name'); // Un fallback

        $data = [
            'ordenSalida' => $ordenSalida,
            // 'items' ya no es necesario si accedes a $ordenSalida->biens directamente en la vista
            'detalleOficina' => $detalleOficina,
            'nombre_oficina_por_defecto' => $nombre_oficina_por_defecto
            // No necesitamos pasar proyecto/acción ni fuente financiamiento aquí a menos que sea relevante
        ];

        $pdf = Pdf::loadView('pdf.orden_salida_pdf', $data);
        $pdf->setPaper('letter', 'portrait'); // Tamaño carta, vertical

        // Lógica de Marca de Agua (si la quieres aquí en lugar de solo CSS)
        $watermarkText = null;
        if ($ordenSalida->estado_orden === 'Anulada') { // Solo para Anulada, por ejemplo
            $watermarkText = 'ANULADA';
        }
        if ($watermarkText) {
            // (Código de la marca de agua con $pdf->getCanvas()->page_script(...) como lo vimos para requisiciones)
        }

        return $pdf->stream('orden-salida-' . $ordenSalida->numero_orden_salida . '.pdf');
    }

    public function generarActaDesincorporacionPdf(SolicitudDesincorporacion $solicitud)
    {
        // Cargar relaciones necesarias (biens, solicitante, aprobador, periodo)
        // Asegúrate de que estas relaciones existan en tu modelo SolicitudDesincorporacion si las usas
        $solicitud->load('biens', 'periodo');
        // if (property_exists($solicitud, 'solicitante')) $solicitud->load('solicitante');
        // if (property_exists($solicitud, 'aprobador')) $solicitud->load('aprobador');


        $detalleOficina = DetalleOficina::first();
        // Asumimos que SolicitudDesincorporacion tiene un campo 'dependencia_solicitante_des'
        if ($detalleOficina && !empty($detalleOficina->autoridades)) {
            foreach ($detalleOficina->autoridades as $autoridad) {
                if (
                    isset($autoridad['nombre']) &&
                    isset($autoridad['cedula'])
                ) {
                    $nombreSolicitante = $autoridad['nombre'];
                    $cedulaSolicitante = $autoridad['cedula'];
                    break;
                }
            }
        }
        // --- FIN LÓGICA AUTORIDAD SOLICITANTE ---
        $ciudad_documento = "Ciudad Bolívar"; // O tomar de configuración

        $data = [
            'solicitud' => $solicitud,
            'detalleOficina' => $detalleOficina,
            'ciudad_documento' => $ciudad_documento,
            'nombreSolicitante' => $nombreSolicitante,
            'cedulaSolicitante' => $cedulaSolicitante,
            // Podrías pasar los nombres de los meses en español si no usas getTranslatedMonthName
            // 'meses' => ['Enero', 'Febrero', ..., 'Diciembre'],
        ];

        $pdf = Pdf::loadView('pdf.acta_desincorporacion_pdf', $data);
        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream('acta-desincorporacion-' . $solicitud->numero_solicitud_des . '.pdf');
    }

    public function generarActaReasignacionPdf(SolicitudReasignacion $solicitud)
    {
        $solicitud->load('biens', 'periodo'); // Carga relaciones que necesites
        // Carga 'solicitante' si lo tienes

        $detalleOficina = DetalleOficina::first();
        $nombre_oficina_por_defecto = config('app.name');

        // Determinar la fecha del acta (ej. fecha de ejecución si existe, sino aprobación, sino solicitud)
        $fechaActa = $solicitud->fecha_ejecucion_rea ?? $solicitud->fecha_aprobacion ?? $solicitud->fecha_solicitud;


        $data = [
            'solicitud' => $solicitud,
            'detalleOficina' => $detalleOficina,
            'nombre_oficina_por_defecto' => $nombre_oficina_por_defecto,
            'fechaActa' => $fechaActa, // Para usar en el PDF si es diferente a fecha_solicitud
            'nombre_moneda' => 'Bs.', // O tu moneda
            // Pasa cualquier otra variable que necesite la vista
        ];

        $pdf = Pdf::loadView('pdf.acta_reasignacion_pdf', $data);
        $pdf->setPaper('letter', 'portrait');

        // Marca de agua si la solicitud está Anulada
        if ($solicitud->estado_solicitud === 'Anulada') {
            $watermarkText = 'ANULADA';
            // (Código de la marca de agua con $pdf->getCanvas()->page_script(...) como lo vimos antes)
        }

        return $pdf->stream('acta-reasignacion-' . $solicitud->numero_solicitud_rea . '.pdf');
    }

    public function generarComunicacionPdf(Comunicacion $comunicacion)
    {
        // Cargar relaciones si son necesarias en el PDF y no se cargan automáticamente
        $comunicacion->load('periodo', 'creador'); // 'creador' es la relación con User

        $detalleOficina = DetalleOficina::first();
        // Aquí puedes pasar más variables si las necesitas en la vista
        // Por ejemplo, un array de meses en español para formatear la fecha
        $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $fechaFormateada = $comunicacion->fecha_documento->format('d') . ' de ' . $meses[$comunicacion->fecha_documento->month - 1] . ' de ' . $comunicacion->fecha_documento->format('Y');


        $data = [
            'comunicacion' => $comunicacion,
            'detalleOficina' => $detalleOficina,
            'fechaDocumentoFormateada' => $fechaFormateada, // Para usar en el PDF
            // ...
        ];

        $pdf = Pdf::loadView('pdf.comunicacion_pdf', $data);
        $pdf->setPaper('letter', 'portrait'); // Tamaño carta

        return $pdf->stream('comunicacion-' . $comunicacion->numero_comunicacion . '.pdf');
    }
}
