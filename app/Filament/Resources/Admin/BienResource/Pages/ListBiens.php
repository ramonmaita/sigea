<?php

namespace App\Filament\Resources\Admin\BienResource\Pages;

use App\Filament\Resources\Admin\BienResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBiens extends ListRecords
{
    protected static string $resource = BienResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
