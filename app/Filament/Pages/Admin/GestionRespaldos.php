<?php

namespace App\Filament\Pages\Admin;

use Filament\Pages\Page;
use Filament\Actions\Action; // Usar Filament\Actions\Action para las acciones de página
use Illuminate\Support\Facades\Artisan;
use Filament\Notifications\Notification;

class GestionRespaldos extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';
    protected static string $view = 'filament.pages.admin.gestion-respaldos';
    protected static ?string $navigationGroup = 'Administración';
    protected static ?string $title = 'Gestión de Respaldos de Base de Datos';
    protected static ?string $navigationLabel = 'Respaldos BD';

    protected static ?int $navigationSort = 10; // Para ordenar en el menú

    // Método para definir las acciones en la cabecera de la página
    protected function getHeaderActions(): array
    {
        return [
            Action::make('crearRespaldoDb')
                ->label('Crear Respaldo (Solo BD)')
                ->icon('heroicon-o-plus-circle')
                ->color('primary')
                ->action(function () {
                    try {
                        // Ejecutar el comando de respaldo
                        Artisan::call('backup:run', ['--only-db' => true, '--disable-notifications' => true]);
                        Notification::make()
                            ->title('Respaldo de BD iniciado')
                            ->body('El proceso de respaldo de la base de datos ha comenzado. Revisa los logs para confirmar su finalización.')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error al iniciar el respaldo')
                            ->danger()
                            ->body('Detalles: ' . $e->getMessage())
                            ->send();
                    }
                })
                ->requiresConfirmation()
                ->modalHeading('Confirmar Creación de Respaldo')
                ->modalDescription('¿Está seguro de que desea iniciar un nuevo respaldo de la base de datos ahora?'),

            Action::make('limpiarRespaldosAntiguos')
                ->label('Limpiar Respaldos Antiguos')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->action(function () {
                     try {
                        Artisan::call('backup:clean', ['--disable-notifications' => true]);
                        Notification::make()
                            ->title('Limpieza de Respaldos Iniciada')
                            ->body('Se ha iniciado el proceso para limpiar los respaldos antiguos según la configuración.')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error al iniciar la limpieza de respaldos')
                            ->danger()
                            ->body('Detalles: ' . $e->getMessage())
                            ->send();
                    }
                })
                ->requiresConfirmation()
                ->modalHeading('Confirmar Limpieza de Respaldos')
                ->modalDescription('¿Está seguro de que desea eliminar los respaldos antiguos según la política de retención configurada?'),
        ];
    }


}
