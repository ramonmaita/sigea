<?php

namespace App\Filament\Resources\Admin;

use App\Filament\Resources\Admin\SolicitudDesincorporacionResource\Pages;
use App\Filament\Resources\Admin\SolicitudDesincorporacionResource\RelationManagers;
use App\Models\Bien;
use App\Models\Periodo;
use App\Models\SolicitudDesincorporacion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\Action; // o Filament\Actions\Action para páginas


class SolicitudDesincorporacionResource extends Resource
{
    protected static ?string $model = SolicitudDesincorporacion::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box-x-mark'; // Ícono para desincorporación
    protected static ?string $modelLabel = 'Solicitud de Desincorporación';
    protected static ?string $pluralModelLabel = 'Solicitudes de Desincorporación';
    protected static ?string $navigationGroup = 'Gestión de Inventario';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la Solicitud')
                    ->columns(2)
                    ->schema([
                        Forms\Components\DatePicker::make('fecha_solicitud')
                            ->label('Fecha de Solicitud')
                            ->default(now())
                            ->required(),
                        Forms\Components\Select::make('periodo_id')
                            ->label('Período de Gestión')
                            ->options(Periodo::query()->orderBy('anio', 'desc')->pluck('nombre', 'id'))
                            ->default(fn() => Periodo::where('estado', 'Abierto')->orderBy('anio', 'desc')->first()?->id)
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('tipo_motivo_desincorporacion')
                            ->label('Motivo de Desincorporación')
                            ->options([
                                'Inservible por Obsolescencia' => 'Inservible por Obsolescencia',
                                'Inservible por Deterioro' => 'Inservible por Deterioro',
                                'Extraviado' => 'Extraviado',
                                'Hurtado' => 'Hurtado',
                                'Donación' => 'Donación',
                                'Venta' => 'Venta',
                                'Otro' => 'Otro',
                            ])
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('justificacion_detallada')
                            ->label('Justificación Detallada del Motivo')
                            ->required()
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Bienes a Desincorporar')
                    ->schema([
                        Forms\Components\Select::make('biens') // Nombre de la relación
                            ->label('Seleccionar Bienes')
                            ->multiple()
                            ->relationship(name: 'biens', titleAttribute: 'nombre')
                            ->getOptionLabelFromRecordUsing(function (Bien $record) { // Cambiado a una función anónima completa para claridad
                                $serial = $record->serial_numero ?? 'N/A'; // Resolvemos el serial primero
                                return "{$record->codigo_bien} - {$record->nombre} (S/N: {$serial})";
                            })
                            ->preload(20)
                            ->searchable(['codigo_bien', 'nombre', 'serial_numero'])
                            // Filtrar bienes que pueden ser desincorporados (ej. no ya desincorporados)
                            ->optionsLimit(200)
                            ->getSearchResultsUsing(function (string $search) {
                                return Bien::whereNotIn('estado_bien', ['Desincorporado', 'Para Desincorporar']) // Evitar seleccionar bienes ya en proceso o desincorporados
                                    ->where(function (Builder $query) use ($search) {
                                        $query->where('nombre', 'like', "%{$search}%")
                                            ->orWhere('codigo_bien', 'like', "%{$search}%")
                                            ->orWhere('serial_numero', 'like', "%{$search}%");
                                    })
                                    ->limit(50)
                                    ->get() // Obtener la colección de modelos Bien
                                    ->mapWithKeys(function ($nombre, $id) { // Para usar getOptionLabelFromRecordUsing
                                        $bien = Bien::find($id);  // Mapear a la estructura deseada
                                        if ($bien) {
                                            $serial = $bien->serial_numero ?? 'N/A';
                                            return [$id => "{$bien->codigo_bien} - {$bien->nombre} (S/N: {$serial})"];
                                        } else {
                                            return [];
                                        }
                                    });
                            })
                            ->helperText('Seleccione uno o más bienes. No se mostrarán bienes ya desincorporados o en proceso.')
                            ->required() // Al menos un bien
                            ->columnSpanFull(),
                        // Para añadir 'observacion_especifica_bien' de la tabla pivote,
                        // necesitaríamos un Relation Manager o una tabla editable en la página de edición/vista.
                        // El Select múltiple solo maneja la asociación.
                    ]),
                Forms\Components\Select::make('estado_solicitud')
                    ->label('Estado de la Solicitud')
                    ->options([
                        'Elaborada' => 'Elaborada',
                        'En Revisión Técnica' => 'En Revisión Técnica',
                        'Aprobada' => 'Aprobada',
                        'Rechazada' => 'Rechazada',
                        'Ejecutada' => 'Ejecutada',
                        'Anulada' => 'Anulada',
                    ])
                    ->default('Elaborada')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('fecha_ejecucion_des')
                    ->label('Fecha de Ejecución de Desincorporación')
                    ->nullable()
                    ->visible(fn(Get $get): bool => $get('estado_solicitud') === 'Ejecutada'), // Solo visible si está ejecutada
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_solicitud_des')->label('N° Solicitud')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('fecha_solicitud')->date('d/m/Y')->label('Fecha Sol.')->sortable(),
                Tables\Columns\TextColumn::make('tipo_motivo_desincorporacion')->label('Motivo')->searchable()->sortable()->limit(30),
                // Tables\Columns\TextColumn::make('solicitante.name')->label('Solicitante')->searchable()->sortable(), // Si tienes la relación
                Tables\Columns\TextColumn::make('estado_solicitud')->label('Estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Elaborada' => 'gray',
                        'En Revisión Técnica' => 'info',
                        'Aprobada' => 'warning', // Warning porque aún no está ejecutada
                        'Rechazada' => 'danger',
                        'Ejecutada' => 'success',
                        'Anulada' => 'primary',
                        default => 'gray',
                    })
                    ->searchable()->sortable(),
                Tables\Columns\TextColumn::make('periodo.nombre')->label('Período')->searchable()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado_solicitud')->options([/* tus estados */]),
                Tables\Filters\SelectFilter::make('tipo_motivo_desincorporacion')->options([/* tus motivos */]),
                Tables\Filters\SelectFilter::make('periodo_id')->relationship('periodo', 'nombre')->label('Período'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                // Acciones para aprobar, ejecutar, etc. vendrán después
                Action::make('aprobarSolicitud')
                    ->label('Aprobar Solicitud')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Aprobar Solicitud de Desincorporación')
                    ->action(function (SolicitudDesincorporacion $record) {
                        $record->estado_solicitud = 'Aprobada';

                        if (property_exists($record, 'fecha_aprobacion')) { // Si tienes fecha_aprobacion
                            $record->fecha_aprobacion = now();
                        }
                        $record->save();
                        Notification::make()->title('Solicitud Aprobada')->success()->send();
                    })
                    ->visible(
                        fn(SolicitudDesincorporacion $record): bool =>
                        in_array($record->estado_solicitud, ['Elaborada', 'En Revisión Técnica']) &&
                            auth()->user()->can('approve', $record) // Usando el método 'approve' de SolicitudDesincorporacionPolicy
                    ),

                Action::make('ejecutarSolicitud')
                    ->label('Ejecutar Solicitud')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Confirmar Ejecución de Desincorporación')
                    ->modalDescription('Esto marcará la solicitud como ejecutada y cambiará el estado de los bienes asociados a "Desincorporado". Esta acción es irreversible para los bienes.')
                    ->action(function (SolicitudDesincorporacion $record) {
                        $record->estado_solicitud = 'Ejecutada';
                        $record->fecha_ejecucion_des = now(); // Asignar fecha de ejecución
                        // Aquí podrías marcar los bienes como desincorporados si es necesario
                        foreach ($record->biens as $bien) {
                            $bien->estado_bien = 'Desincorporado'; // Cambiar el estado del bien
                            $bien->save();
                        }
                        $record->save();
                        Notification::make()->title('Solicitud Ejecutada')->success()->send();
                    })
                    ->visible(
                        fn(SolicitudDesincorporacion $record): bool =>
                        in_array($record->estado_solicitud, ['Aprobada']) &&
                            auth()->user()->can('execute', $record) // Usando el método 'approve' de SolicitudDesincorporacionPolicy
                    ),

                Action::make('pdfActaDesincorporacion')
                    ->label('Ver Acta PDF')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->modalContent(fn(SolicitudDesincorporacion $record): \Illuminate\Contracts\View\View => view(
                        'filament.modals.view-pdf', // Reutilizar el modal genérico
                        ['pdfUrl' => route('solicitudes_desincorporacion.acta_pdf', $record)]
                    ))
                    ->modalHeading(fn(SolicitudDesincorporacion $record): string => 'Acta de Desincorporación N° ' . $record->numero_solicitud_des)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->modalWidth('5xl') // O el tamaño que prefieras
                    ->visible(
                        fn(SolicitudDesincorporacion $record): bool =>
                        $record->estado_solicitud === 'Ejecutada' && auth()->user()->can('viewPdf', $record) // Usando método de policy
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('fecha_solicitud', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSolicitudDesincorporacions::route('/'),
            'create' => Pages\CreateSolicitudDesincorporacion::route('/create'),
            'edit' => Pages\EditSolicitudDesincorporacion::route('/{record}/edit'),
        ];
    }
}
