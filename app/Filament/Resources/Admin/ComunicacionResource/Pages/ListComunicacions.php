<?php

namespace App\Filament\Resources\Admin\ComunicacionResource\Pages;

use App\Filament\Resources\Admin\ComunicacionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListComunicacions extends ListRecords
{
    protected static string $resource = ComunicacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
