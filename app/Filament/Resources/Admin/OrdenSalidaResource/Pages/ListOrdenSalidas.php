<?php

namespace App\Filament\Resources\Admin\OrdenSalidaResource\Pages;

use App\Filament\Resources\Admin\OrdenSalidaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrdenSalidas extends ListRecords
{
    protected static string $resource = OrdenSalidaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
