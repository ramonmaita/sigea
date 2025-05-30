<?php

namespace App\Filament\Resources\Admin\ComunicacionResource\Pages;

use App\Filament\Resources\Admin\ComunicacionResource;
use App\Models\Comunicacion;
use App\Models\DetalleOficina;
use App\Models\Periodo;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateComunicacion extends CreateRecord
{
    protected static string $resource = ComunicacionResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index'); // Redirigir a la lista
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

        $prefijo = "COM"; // Prefijo para Comunicaciones
        $prefijoCompleto = "{$acronimoOficina}-{$prefijo}-{$anio}-";

        $ultimoNumero = Comunicacion::where('periodo_id', $data['periodo_id'])
            ->where('numero_comunicacion', 'like', "{$prefijoCompleto}%")
            ->lockForUpdate()
            ->selectRaw('MAX(CAST(SUBSTRING_INDEX(numero_comunicacion, "-", -1) AS UNSIGNED)) as max_num')
            ->value('max_num');

        $siguienteNumero = ($ultimoNumero === null ? 0 : $ultimoNumero) + 1;

        $data['numero_comunicacion'] = $prefijoCompleto . str_pad($siguienteNumero, 4, '0', STR_PAD_LEFT);

        // --- Valores por defecto adicionales ---
        if (!isset($data['user_id_creador']) && auth()->check()) {
            $data['user_id_creador'] = auth()->id();
        }
        if (!isset($data['fecha_documento'])) {
            $data['fecha_documento'] = now()->toDateString();
        }
        if (!isset($data['estado'])) {
            $data['estado'] = 'Borrador';
        }

        // Procesar el firmante seleccionado
        if (isset($data['firmante_seleccionado'])) {
            list($nombre, $cargo) = explode('|', $data['firmante_seleccionado'], 2);
            $data['firmante_nombre'] = trim($nombre);
            $data['firmante_cargo'] = trim($cargo);
        }
        unset($data['firmante_seleccionado']); // Eliminar el campo virtual

        return $data;
    }
}
