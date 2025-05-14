<?php

namespace App\Filament\Resources\Admin\RequisicionResource\Pages;

use App\Filament\Resources\Admin\RequisicionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder; // Importar Builder
use Illuminate\Support\Facades\Auth; // Importar Auth


class ListRequisicions extends ListRecords
{
    protected static string $resource = RequisicionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder // En Filament v3 es getTableQuery()
    {
        $query = parent::getTableQuery();
        $user = Auth::user();

        if ($user->hasPermissionTo('view_any_requisiciones')) {
            return $query; // El admin o quien tenga permiso ve todo
        }

        if ($user->hasPermissionTo('view_own_requisiciones')) {
            return $query->where('user_id', $user->id); // Solo ve las propias
        }

        // Si no tiene ninguno de los dos permisos, no debería ver nada.
        // Filament también respeta el viewAny de la policy.
        // Esta lógica aquí es una capa adicional para el query de la tabla.
        return $query->whereRaw('1 = 0'); // No devuelve nada si no tiene permiso explícito aquí
    }
}
