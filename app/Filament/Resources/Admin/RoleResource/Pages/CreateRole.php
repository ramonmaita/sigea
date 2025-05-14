<?php

namespace App\Filament\Resources\Admin\RoleResource\Pages;

use App\Filament\Resources\Admin\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index'); // 'index' es la ruta de la página de listado del recurso
    }
}
