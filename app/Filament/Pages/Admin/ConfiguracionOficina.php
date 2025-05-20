<?php

namespace App\Filament\Pages\Admin;

use App\Models\DetalleOficina; // <--- USA TU MODELO AQUÍ
use Filament\Forms\Components\Section; // Para agrupar los nuevos campos
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\Toggle;   // Para el campo 'es_predeterminado'

// use Illuminate\Contracts\Auth\Authenticatable;

class ConfiguracionOficina extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static string $view = 'filament.pages.admin.configuracion-oficina';
    protected static ?string $navigationGroup = 'Administración';
    protected static ?string $title = 'Configuración de la Oficina';
    protected static ?string $navigationLabel = 'Datos de la Oficina';

    public ?array $data = [];
    public ?DetalleOficina $detalleOficina = null; // <--- USA TU MODELO AQUÍ

    public static function canAccess(array $parameters = []): bool
    {
        // Verifica si el usuario autenticado tiene el rol 'Administrador'
        if (!auth()->check()) {
            return false; // No autenticado, no puede acceder
        }
        return auth()->user()->hasRole('Administrador');
    }

    public function mount(): void
    {
        // Cargar el primer (y único) registro de DetalleOficina, o crear uno si no existe
        $this->detalleOficina = DetalleOficina::firstOrCreate([]); // <--- USA TU MODELO AQUÍ
        $this->form->fill($this->detalleOficina->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nombre_oficina')
                    ->label('Nombre de la Oficina/Institución')
                    ->required(),
                TextInput::make('acronimo_oficina') // <-- NUEVO CAMPO
                    ->label('Acrónimo de la Oficina (para correlativos)')
                    ->helperText('Ej: DIRCAA, UPTBOLIVAR, ADM')
                    ->maxLength(50)
                    ->nullable(),
                Textarea::make('direccion')
                    ->label('Dirección')
                    ->rows(2),
                TextInput::make('telefonos')
                    ->label('Teléfonos de Contacto'),
                TextInput::make('email_contacto')
                    ->label('Email de Contacto')
                    ->email(),
                FileUpload::make('path_logo')
                    ->label('Logo de la Institución')
                    ->image()
                    ->disk('public')
                    ->directory('logos-oficina') // Directorio dentro del disco público
                    ->visibility('public')
                    ->columnSpanFull(),
                Repeater::make('autoridades')
                    ->label('Autoridades Principales')
                    ->schema([
                        TextInput::make('nombre')->label('Nombre Completo')->required(),
                        TextInput::make('cargo')->label('Cargo')->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->defaultItems(1),
                Section::make('Proyectos / Acciones Específicas')
                    ->description('Defina la lista de proyectos o acciones que se pueden asociar a las requisiciones. Marque uno como predeterminado para nuevas requisiciones.')
                    ->collapsible() // Para que se pueda plegar/desplegar
                    ->schema([
                        Repeater::make('proyectos_acciones')
                            ->label(false) // El label ya está en la sección
                            ->schema([
                                TextInput::make('codigo')
                                    ->label('Código del Proyecto/Acción')
                                    ->required(),
                                TextInput::make('nombre')
                                    ->label('Nombre del Proyecto/Acción')
                                    ->required(),
                                TextInput::make('responsable')
                                    ->label('Responsable del Proyecto/Acción')
                                    ->required(),
                                Toggle::make('es_predeterminado')
                                    ->label('Predeterminado')
                                    ->helperText('Marcar si este es el valor por defecto.')
                                    ->inline(false), // Para que el label esté encima
                            ])
                            ->defaultItems(0) // Iniciar vacío o con 1 si prefieres
                            ->addActionLabel('Añadir Proyecto/Acción')
                            ->columns(2), // Campos dentro de cada item del repeater
                    ]),

                // --- NUEVA SECCIÓN PARA FUENTES DE FINANCIAMIENTO ---
                Section::make('Fuentes de Financiamiento')
                    ->description('Defina la lista de fuentes de financiamiento. Marque una como predeterminada.')
                    ->collapsible()
                    ->schema([
                        Repeater::make('fuentes_financiamiento')
                            ->label(false)
                            ->schema([
                                TextInput::make('nombre')
                                    ->label('Nombre de la Fuente')
                                    ->required(),
                                TextInput::make('codigo_interno')
                                    ->label('Código Interno (Opcional)'),
                                Toggle::make('es_predeterminado')
                                    ->label('Predeterminado')
                                    ->helperText('Marcar si este es el valor por defecto.')
                                    ->inline(false),
                            ])
                            ->defaultItems(0)
                            ->addActionLabel('Añadir Fuente de Financiamiento'),
                    ]),

            ])
            ->statePath('data')
            ->model($this->detalleOficina); // <--- USA TU MODELO AQUÍ (opcional, pero bueno para la carga inicial)
    }

    public function save(): void
    {
        try {
            $formData = $this->form->getState();
            $this->detalleOficina->update($formData); // <--- USA TU MODELO AQUÍ

            Notification::make()
                ->title('Datos guardados exitosamente')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al guardar los datos')
                ->body($e->getMessage()) // Sé cuidadoso mostrando mensajes de error directamente en producción
                ->danger()
                ->send();
        }
    }
}
