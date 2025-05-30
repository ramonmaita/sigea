<?php

namespace App\Filament\Resources\Admin\SolicitudDesincorporacionResource\Pages;

use App\Filament\Resources\Admin\SolicitudDesincorporacionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSolicitudDesincorporacion extends EditRecord
{
    protected static string $resource = SolicitudDesincorporacionResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
