<?php

namespace App\Filament\Resources\Admin\SolicitudReasignacionResource\Pages;

use App\Filament\Resources\Admin\SolicitudReasignacionResource;
use App\Models\DetalleOficina;
use App\Models\Periodo;
use App\Models\SolicitudReasignacion;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSolicitudReasignacion extends CreateRecord
{
    protected static string $resource = SolicitudReasignacionResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $detalleOficina = DetalleOficina::first();
        $acronimoOficina = 'SIGEA'; // Valor por defecto
        if ($detalleOficina && !empty($detalleOficina->acronimo_oficina)) {
            $acronimoOficina = $detalleOficina->acronimo_oficina;
        }

        $periodoId = $data['periodo_id'] ?? null;
        $anio = date('Y');

        if ($periodoId) {
            $periodoSeleccionado = Periodo::find($periodoId);
            if ($periodoSeleccionado && isset($periodoSeleccionado->anio)) {
                $anio = $periodoSeleccionado->anio;
            }
        } else {
            $periodoActivo = Periodo::where('estado', 'Abierto')->orderBy('anio', 'desc')->first();
            if ($periodoActivo) {
                $anio = $periodoActivo->anio;
                $data['periodo_id'] = $periodoActivo->id;
            }
        }

        $prefijoCompleto = "{$acronimoOficina}-SREA-{$anio}-"; // SREA para Solicitud REAsignación

        $ultimoNumero = SolicitudReasignacion::where('periodo_id', $data['periodo_id'])
            ->where('numero_solicitud_rea', 'like', "{$prefijoCompleto}%")
            ->lockForUpdate()
            ->selectRaw('MAX(CAST(SUBSTRING_INDEX(numero_solicitud_rea, "-", -1) AS UNSIGNED)) as max_num')
            ->value('max_num');

        $siguienteNumero = ($ultimoNumero === null ? 0 : $ultimoNumero) + 1;

        $data['numero_solicitud_rea'] = $prefijoCompleto . str_pad($siguienteNumero, 4, '0', STR_PAD_LEFT);

        if (!isset($data['fecha_solicitud'])) {
            $data['fecha_solicitud'] = now()->toDateString();
        }
        if (!isset($data['estado_solicitud'])) {
            $data['estado_solicitud'] = 'Elaborada';
        }

        return $data;
    }
}
