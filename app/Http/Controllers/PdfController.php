<?php

namespace App\Http\Controllers;

use App\Models\DetalleOficina;
use App\Models\Requisicion;
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
}
