<?php

namespace App\Filament\Resources\Admin\RequisicionResource\Pages;

use App\Filament\Resources\Admin\RequisicionResource;
use App\Models\Periodo;
use App\Models\Requisicion;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateRequisicion extends CreateRecord
{
    protected static string $resource = RequisicionResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Obtener el año del período seleccionado
        $periodoSeleccionado = Periodo::find($data['periodo_id']);
        $anio = $periodoSeleccionado ? $periodoSeleccionado->anio : date('Y'); // Fallback al año actual si no hay período

        // Generar el próximo correlativo para el período
        $ultimoNumero = Requisicion::where('periodo_id', $data['periodo_id'])
            ->where('numero_requisicion', 'like', "REQ-{$anio}-%") // Asumiendo prefijo REQ-
            ->selectRaw('MAX(CAST(SUBSTRING_INDEX(numero_requisicion, "-", -1) AS UNSIGNED)) as max_num')
            ->value('max_num');

        $siguienteNumero = ($ultimoNumero ?? 0) + 1;
        $data['numero_requisicion'] = "REQ-{$anio}-" . str_pad($siguienteNumero, 4, '0', STR_PAD_LEFT);

        // Si el usuario_id no está en $data pero debería ser el usuario autenticado
        if (!isset($data['user_id'])) {
            $data['user_id'] = auth()->id();
        }
        // Si la fecha_solicitud no está en $data
        if (!isset($data['fecha_solicitud'])) {
            $data['fecha_solicitud'] = now()->toDateString();
        }
        // Si el estado no está en $data
        if (!isset($data['estado'])) {
            $data['estado'] = 'Borrador';
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index'); // 'index' es la ruta de la página de listado del recurso
    }
}
