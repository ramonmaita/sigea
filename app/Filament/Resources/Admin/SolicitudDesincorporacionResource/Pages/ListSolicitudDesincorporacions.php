<?php

namespace App\Filament\Resources\Admin\SolicitudDesincorporacionResource\Pages;

use App\Filament\Resources\Admin\SolicitudDesincorporacionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSolicitudDesincorporacions extends ListRecords
{
    protected static string $resource = SolicitudDesincorporacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
