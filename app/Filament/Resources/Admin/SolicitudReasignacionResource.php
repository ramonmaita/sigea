<?php

namespace App\Filament\Resources\Admin;

use App\Filament\Resources\Admin\SolicitudReasignacionResource\Pages;
use App\Filament\Resources\Admin\SolicitudReasignacionResource\RelationManagers;
use App\Models\Bien;
use App\Models\Periodo;
use App\Models\SolicitudReasignacion;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action as ActionsAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class SolicitudReasignacionResource extends Resource
{
    protected static ?string $model = SolicitudReasignacion::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left'; // Ícono para reasignación
    protected static ?string $modelLabel = 'Solicitud de Reasignación';
    protected static ?string $pluralModelLabel = 'Solicitudes de Reasignación';
    protected static ?string $navigationGroup = 'Gestión de Inventario';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información General de la Solicitud')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('fecha_solicitud')
                            ->label('Fecha de Solicitud')
                            ->default(now())
                            ->required(),
                        Select::make('periodo_id')
                            ->label('Período de Gestión')
                            ->relationship('periodo', 'nombre') // Asume relación 'periodo' en SolicitudReasignacion
                            ->default(function () {
                                // Intentar obtener el período activo o el más reciente
                                $periodoActivo = Periodo::where('estado', Periodo::ESTADO_ACTIVO)->first();
                                return $periodoActivo ? $periodoActivo->id : null;
                            })
                            ->searchable()
                            ->required(),
                    ]),
                Section::make('Detalles de la Reasignación')
                    ->columns(3)
                    ->schema([
                        TextInput::make('unidad_administrativa_origen')
                            ->label('Unidad Administrativa Origen')
                            ->maxLength(255)
                            ->helperText('Unidad donde se encuentran actualmente los bienes. Podría autocompletarse al seleccionar bienes.'),
                        TextInput::make('responsable_actual_origen')
                            ->label('Responsable Actual en Origen (Nombre)')
                            ->nullable()
                            ->maxLength(255),
                        TextInput::make('cedula_responsable_origen')
                            ->label('Cédula Responsable Origen')
                            ->nullable()
                            ->maxLength(20),

                        TextInput::make('unidad_administrativa_destino')
                            ->label('Nueva Unidad Administrativa Destino')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('responsable_destino')
                            ->label('Nuevo Responsable en Destino (Nombre)')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('cedula_responsable_destino')
                            ->label('Cédula Nuevo Responsable Destino')
                            ->nullable()
                            ->maxLength(20),
                        Textarea::make('motivo_reasignacion') // Movido aquí para más espacio
                            ->label('Motivo de la Reasignación')
                            ->required()
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('Bienes a Reasignar')
                    ->schema([
                        Select::make('biens') // Nombre de la relación en el modelo SolicitudReasignacion
                            ->label('Seleccionar Bienes')
                            ->multiple()
                            ->relationship(name: 'biens', titleAttribute: 'nombre')
                            ->getOptionLabelFromRecordUsing(fn(Bien $record) => "{$record->codigo_bien} - {$record->nombre} (Actual: {$record->ubicacion_actual})")
                            ->preload(20)
                            ->searchable(['codigo_bien', 'nombre', 'serial_numero', 'ubicacion_actual'])
                            ->optionsLimit(200)
                            ->getSearchResultsUsing(function (string $search) {
                                // Mostrar bienes que NO estén 'Desincorporado' o 'Para Desincorporar'
                                return Bien::whereNotIn('estado_bien', ['Desincorporado', 'Para Desincorporar'])
                                    ->where(function (Builder $query) use ($search) {
                                        $query->where('nombre', 'like', "%{$search}%")
                                            ->orWhere('codigo_bien', 'like', "%{$search}%")
                                            ->orWhere('serial_numero', 'like', "%{$search}%")
                                            ->orWhere('ubicacion_actual', 'like', "%{$search}%");
                                    })
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(function (Bien $bien) {
                                        return [$bien->id => "{$bien->codigo_bien} - {$bien->nombre} (Actual: {$bien->ubicacion_actual} / Estado: {$bien->estado_bien})"];
                                    });
                            })
                            ->helperText('Seleccione uno o más bienes. No se mostrarán bienes ya desincorporados.')
                            ->required()
                            ->columnSpanFull(),
                        // Para 'observacion_especifica_bien' de la tabla pivote,
                        // lo ideal es un Relation Manager. Lo dejaremos pendiente por ahora.
                    ]),
                Section::make('Estado de la Solicitud')
                    ->schema([
                        Select::make('estado_solicitud')
                            ->label('Estado de la Solicitud')
                            ->options([
                                'Elaborada' => 'Elaborada',
                                'Aprobada' => 'Aprobada',
                                'Rechazada' => 'Rechazada',
                                'Ejecutada' => 'Ejecutada',
                                'Anulada' => 'Anulada',
                            ])
                            ->default('Elaborada')
                            ->required(),
                        // DatePicker::make('fecha_ejecucion_rea') // Si lo añadiste a la migración
                        //     ->label('Fecha de Ejecución de Reasignación')
                        //     ->nullable()
                        //     ->visible(fn (Get $get): bool => $get('estado_solicitud') === 'Ejecutada'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_solicitud_rea')->label('N° Solicitud')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('fecha_solicitud')->date('d/m/Y')->label('Fecha Sol.')->sortable(),
                Tables\Columns\TextColumn::make('unidad_administrativa_destino')->label('Nueva Unidad Destino')->searchable()->sortable()->limit(30),
                Tables\Columns\TextColumn::make('responsable_destino')->label('Nuevo Responsable')->searchable()->sortable()->limit(30),
                Tables\Columns\TextColumn::make('estado_solicitud')->label('Estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Elaborada' => 'gray',
                        'Aprobada' => 'warning',
                        'Rechazada' => 'danger',
                        'Ejecutada' => 'success',
                        'Anulada' => 'primary',
                        default => 'gray',
                    })
                    ->searchable()->sortable(),
                Tables\Columns\TextColumn::make('periodo.nombre')->label('Período')->searchable()->sortable(),
                // Tables\Columns\TextColumn::make('solicitante.name')->label('Solicitante')->searchable()->sortable(), // Si tienes la relación
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado_solicitud')->options([/* tus estados */]),
                Tables\Filters\SelectFilter::make('periodo_id')->relationship('periodo', 'nombre')->label('Período'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                ActionsAction::make('pdfActaReasignacion')
                    ->label('Ver Acta PDF')
                    ->icon('heroicon-o-document-duplicate') // O uno más específico para actas
                    ->color('success')
                    ->modalContent(fn(SolicitudReasignacion $record): \Illuminate\Contracts\View\View => view(
                        'filament.modals.view-pdf', // Reutilizamos la vista del modal
                        ['pdfUrl' => route('solicitudes_reasignacion.acta_pdf', $record)]
                    ))
                    ->modalHeading(fn(SolicitudReasignacion $record): string => 'Acta de Reasignación N° ' . $record->numero_solicitud_rea)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->modalWidth('6xl') // Ajusta el tamaño
                    ->visible(
                        fn(SolicitudReasignacion $record): bool =>
                        // Mostrar si está Ejecutada O Aprobada (quizás se quiere ver un borrador del acta)
                        in_array($record->estado_solicitud, ['Ejecutada', 'Aprobada']) &&
                            auth()->user()->can('viewPdf', $record) // Usando el método de policy
                    ),

                ActionsAction::make('ejecutarReasignacion')
                    ->label('Ejecutar Reasignación')
                    ->icon('heroicon-o-check-badge') // O un ícono apropiado
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Confirmar Ejecución de Reasignación')
                    ->modalDescription('Esta acción marcará la solicitud como ejecutada y actualizará la ubicación de los bienes asociados. Esta acción es irreversible para esta solicitud.')
                    ->action(function (SolicitudReasignacion $record) {
                        DB::transaction(function () use ($record) {
                            $record->estado_solicitud = 'Ejecutada';
                            $record->save();

                            // Actualizar la ubicación de los bienes asociados
                            foreach ($record->biens as $bien) { // Asume que la relación se llama 'biens'
                                $bien->ubicacion_actual = $record->unidad_administrativa_destino; // O el campo que corresponda
                                $bien->save();
                            }
                        });
                        Notification::make()->title('Reasignación Ejecutada Exitosamente')->success()->send();
                    })
                    ->visible(
                        fn(SolicitudReasignacion $record): bool =>
                        $record->estado_solicitud === 'Aprobada' &&
                            auth()->user()->can('execute', $record) // Usando el método 'execute' de tu SolicitudReasignacionPolicy
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
            'index' => Pages\ListSolicitudReasignacions::route('/'),
            'create' => Pages\CreateSolicitudReasignacion::route('/create'),
            'edit' => Pages\EditSolicitudReasignacion::route('/{record}/edit'),
        ];
    }
}
