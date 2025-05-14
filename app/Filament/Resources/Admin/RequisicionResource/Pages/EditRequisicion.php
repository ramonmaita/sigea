<?php

namespace App\Filament\Resources\Admin\RequisicionResource\Pages;

use App\Filament\Resources\Admin\RequisicionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRequisicion extends EditRecord
{
    protected static string $resource = RequisicionResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\DeleteAction::make(),
    //     ];
    // }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index'); // 'index' es la ruta de la página de listado del recurso
    }
}
