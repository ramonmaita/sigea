<?php

namespace App\Filament\Resources\Admin\ComunicacionResource\Pages;

use App\Filament\Resources\Admin\ComunicacionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditComunicacion extends EditRecord
{
    protected static string $resource = ComunicacionResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index'); // Redirigir a la lista
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
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

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }
}
