<?php

namespace App\Filament\Resources\Admin\SolicitudReasignacionResource\Pages;

use App\Filament\Resources\Admin\SolicitudReasignacionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSolicitudReasignacion extends EditRecord
{
    protected static string $resource = SolicitudReasignacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
