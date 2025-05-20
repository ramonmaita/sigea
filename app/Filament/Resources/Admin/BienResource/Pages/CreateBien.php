<?php

namespace App\Filament\Resources\Admin\BienResource\Pages;

use App\Filament\Resources\Admin\BienResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBien extends CreateRecord
{
    protected static string $resource = BienResource::class;
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
