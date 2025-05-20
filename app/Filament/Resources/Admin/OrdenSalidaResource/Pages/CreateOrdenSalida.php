<?php

namespace App\Filament\Resources\Admin\OrdenSalidaResource\Pages;

use App\Filament\Resources\Admin\OrdenSalidaResource;
use App\Models\DetalleOficina;
use App\Models\OrdenSalida;
use App\Models\Periodo;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOrdenSalida extends CreateRecord
{
    protected static string $resource = OrdenSalidaResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $detalleOficina = DetalleOficina::first();
        $acronimoOficina = $detalleOficina?->acronimo_oficina ?: 'OFIC'; // Acrónimo por defecto

        $periodoId = $data['periodo_id'] ?? null;
        $anio = 'XXXX';

        if ($periodoId) {
            $periodoSeleccionado = Periodo::find($periodoId);
            if ($periodoSeleccionado) {
                $anio = $periodoSeleccionado->anio;
            }
        } else {
            $periodoActivo = Periodo::where('estado', 'Abierto')->orderBy('anio', 'desc')->first();
            if ($periodoActivo) {
                $anio = $periodoActivo->anio;
                $data['periodo_id'] = $periodoActivo->id;
            } else {
                 $anio = date('Y'); // Fallback si no hay período activo
            }
        }

        $prefijoCompleto = "{$acronimoOficina}-OS-{$anio}-";

        $ultimoNumero = OrdenSalida::where('periodo_id', $data['periodo_id'])
            ->where('numero_orden_salida', 'like', "{$prefijoCompleto}%")
            ->lockForUpdate()
            ->selectRaw('MAX(CAST(SUBSTRING_INDEX(numero_orden_salida, "-", -1) AS UNSIGNED)) as max_num')
            ->value('max_num');

        $siguienteNumero = ($ultimoNumero ?? 0) + 1;
        $data['numero_orden_salida'] = $prefijoCompleto . str_pad($siguienteNumero, 4, '0', STR_PAD_LEFT);

        // Valores por defecto si no se establecieron (user_id_solicitante si lo hubieras añadido)
        // if(!isset($data['user_id_solicitante'])) {
        //     $data['user_id_solicitante'] = auth()->id();
        // }
        if(!isset($data['fecha_salida'])) {
            $data['fecha_salida'] = now()->toDateString();
        }
        if(!isset($data['estado_orden'])) {
            $data['estado_orden'] = 'Solicitada';
        }

        return $data;
    }
}
