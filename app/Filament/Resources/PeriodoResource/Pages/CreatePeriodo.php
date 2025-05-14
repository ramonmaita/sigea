<?php

namespace App\Filament\Resources\PeriodoResource\Pages;

use App\Filament\Resources\PeriodoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePeriodo extends CreateRecord
{
    protected static string $resource = PeriodoResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index'); // 'index' es la ruta de la página de listado del recurso
    }
}
