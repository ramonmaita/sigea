<?php

namespace App\Filament\Resources\Admin\SolicitudReasignacionResource\Pages;

use App\Filament\Resources\Admin\SolicitudReasignacionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSolicitudReasignacions extends ListRecords
{
    protected static string $resource = SolicitudReasignacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
