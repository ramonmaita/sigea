<?php

namespace App\Filament\Resources\Admin;

use App\Filament\Resources\Admin\RequisicionResource\Pages;
use App\Filament\Resources\Admin\RequisicionResource\RelationManagers;
use App\Models\Requisicion;
use App\Models\DetalleOficina; // Para obtener proyectos y fuentes
use App\Models\Periodo;       // Tu modelo de Periodo
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Get; // Para campos reactivos
use Filament\Forms\Set; // Para campos reactivos
use Illuminate\Support\Str; // Para el correlativo
use Carbon\Carbon; // Para fechas
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Storage;

class RequisicionResource extends Resource
{
    protected static ?string $model = Requisicion::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text'; // O el que prefieras
    protected static ?string $modelLabel = 'Requisición';
    protected static ?string $pluralModelLabel = 'Requisiciones';
    protected static ?string $navigationGroup = 'Gestión Documental'; // O un nuevo grupo

    public static function form(Form $form): Form
    {
        // Cargar datos de DetalleOficina para los Selects
        $detalleOficina = DetalleOficina::first();
        $proyectosAcciones = $detalleOficina && $detalleOficina->proyectos_acciones ?
            collect($detalleOficina->proyectos_acciones)->pluck('nombre', 'codigo')->all() : [];
        $fuentesFinanciamiento = $detalleOficina && $detalleOficina->fuentes_financiamiento ?
            collect($detalleOficina->fuentes_financiamiento)->pluck('nombre', 'nombre')->all() : []; // Asumiendo que guardamos el nombre

        // Encontrar los predeterminados
        $defaultProyectoCodigo = null;
        if ($detalleOficina && $detalleOficina->proyectos_acciones) {
            foreach ($detalleOficina->proyectos_acciones as $pa) {
                if (isset($pa['es_predeterminado']) && $pa['es_predeterminado']) {
                    $defaultProyectoCodigo = $pa['codigo'];
                    break;
                }
            }
        }
        $defaultFuenteNombre = null;
        if ($detalleOficina && $detalleOficina->fuentes_financiamiento) {
            foreach ($detalleOficina->fuentes_financiamiento as $ff) {
                if (isset($ff['es_predeterminado']) && $ff['es_predeterminado']) {
                    $defaultFuenteNombre = $ff['nombre'];
                    break;
                }
            }
        }

        $descripcionesExistentes = \App\Models\RequisicionItem::query()
            ->select('descripcion')
            ->distinct()
            ->pluck('descripcion')
            ->toArray();

        return $form
            ->schema([
                Section::make('Información General')
                    ->columns(4)
                    ->schema([
                        DatePicker::make('fecha_solicitud')
                            ->label('Fecha de Solicitud')
                            ->default(now())
                            ->required(),
                        Select::make('periodo_id') // Usando tu FK 'periodo_id'
                            ->label('Período de Gestión')
                            ->options(Periodo::query()->orderBy('anio', 'desc')->pluck('nombre', 'id'))
                            ->default(function () {
                                // Intentar obtener el período activo o el más reciente
                                $periodoActivo = Periodo::where('estado', Periodo::ESTADO_ACTIVO)->first();
                                return $periodoActivo ? $periodoActivo->id : null;
                            })
                            ->searchable()
                            ->required(),
                        TextInput::make('dependencia_solicitante')
                            ->label('Dependencia Solicitante')
                            ->required()
                            ->columnSpan(2)
                            ->maxLength(255),
                        Select::make('tipo_requisicion')
                            ->label('Tipo de Requisición')
                            ->options([ // Las opciones de tu enum
                                'BIENES' => 'Bienes',
                                'SERVICIO' => 'Servicio',
                                'MATERIALES Y SUMINISTROS' => 'Materiales y Suministros',
                            ])
                            ->columnSpan(2)
                            ->required(),
                        Select::make('estado')
                            ->label('Estado')
                            ->options([ // Las opciones de tu enum
                                'Borrador' => 'Borrador',
                                'Enviada' => 'Enviada',
                                'Aprobada' => 'Aprobada',
                                'Rechazada' => 'Rechazada',
                                'Procesada' => 'Procesada',
                                'Anulada' => 'Anulada',
                            ])
                            ->default('Borrador')
                            ->required(),
                    ]),

                Section::make('Detalles del Proyecto y Financiamiento')
                    ->columns(2)
                    ->schema([
                        Select::make('proyecto_accion_codigo')
                            ->label('Código Proyecto/Acción')
                            ->options($proyectosAcciones)
                            ->default($defaultProyectoCodigo)
                            ->searchable()
                            // Podríamos añadir ->reactive() y afterStateUpdated para mostrar nombre y responsable dinámicamente
                            ->helperText('Seleccione un proyecto/acción de la lista configurada.'),
                        Select::make('fuente_financiamiento_nombre')
                            ->label('Fuente de Financiamiento')
                            ->options($fuentesFinanciamiento)
                            ->default($defaultFuenteNombre)
                            ->searchable()
                            ->helperText('Seleccione una fuente de la lista configurada.'),
                    ]),

                Textarea::make('justificacion_uso')
                    ->label('Justificación del Uso o Destino')
                    ->required()
                    ->rows(2)
                    ->columnSpanFull(),

                Section::make('Ítems de la Requisición')
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items') // Nombre de la relación en el modelo Requisicion
                            ->label(false)
                            ->schema([
                                TextInput::make('cantidad')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0.01)
                                    ->step(0.01), // Para decimales
                                TextInput::make('unidad_medida')
                                    ->label('Unidad de Medida')
                                    ->maxLength(30)
                                    ->required()
                                    ->datalist([ // Sugerencias comunes
                                        'Unidad',
                                        'Caja',
                                        'Paquete',
                                        'Metro',
                                        'Litro',
                                        'Kilogramo',
                                        'Servicio',
                                        'Global'
                                    ]),
                                TextInput::make('descripcion') // Tu campo 'descripcion'
                                    ->label('Descripción del Artículo/Servicio')
                                    ->required()
                                    ->minLength(3)
                                    ->maxLength(100)
                                    ->columnSpan(2)
                                    ->datalist($descripcionesExistentes) // Añadir esto
                                    ->live(onBlur: true) // Para que reaccione cuando se pierde el foco
                                    // ->live(debounce: 500) // O con debounce para que reaccione mientras se escribe
                                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                                        if ($state) {
                                            // Buscar el ítem completo que coincida con la descripción
                                            $itemExistente = \App\Models\RequisicionItem::where('descripcion', $state)
                                                ->orderBy('created_at', 'desc') // Tomar el más reciente por si hay varios
                                                ->first();
                                            if ($itemExistente) {
                                                $set('unidad_medida', $itemExistente->unidad_medida);
                                                $set('precio_unitario', $itemExistente->precio_unitario);
                                            }
                                        }
                                    }),
                                TextInput::make('precio_unitario') // Tu campo 'precio_unitario'
                                    ->label('Precio Unitario (Ref.)')
                                    ->numeric()
                                    ->gte(0)
                                    ->prefix('Bs.') // O tu moneda
                                    ->minValue(0)
                                    ->step(0.01), // Para decimales
                            ])
                            ->defaultItems(1)
                            ->addActionLabel('Añadir Ítem')
                            ->columns(5) // Ajusta las columnas para los campos del ítem
                            ->columnSpanFull()
                            // Lógica para autocompletar items (más avanzada, para después)
                            // ->reorderableWithButtons() // Opcional: permitir reordenar ítems
                            ->cloneable() // Opcional: permitir clonar ítems
                            ->deleteAction(
                                fn(\Filament\Forms\Components\Actions\Action $action) => $action->requiresConfirmation(),
                            ),
                    ]),

                Textarea::make('observaciones')
                    ->label('Observaciones Adicionales')
                    ->rows(3)
                    ->columnSpanFull(),

                FileUpload::make('path_archivo_adjunto')
                    ->label('Adjuntar Archivo (Opcional)')
                    ->disk('public')
                    ->directory('adjuntos_requisiciones/' . date('Y/m'))
                    ->visibility('public')
                    ->acceptedFileTypes(['application/pdf', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'])
                    ->maxSize(5120) // 5MB
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('periodo.nombre') // Asumiendo relación 'periodo' y campo 'nombre' en Periodo
                    ->label('Período')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('numero_requisicion')
                    ->label('N° Requisición')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_solicitud')
                    ->label('Fecha Sol.')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('dependencia_solicitante')
                    ->label('Dependencia')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(fn($record) => $record->dependencia_solicitante),
                Tables\Columns\TextColumn::make('tipo_requisicion')
                    ->label('Tipo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Borrador' => 'gray',
                        'Enviada' => 'warning',
                        'Aprobada' => 'success',
                        'Rechazada' => 'danger',
                        'Procesada' => 'info',
                        'Anulada' => 'primary',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('path_archivo_adjunto')
                    ->label('a')
                    ->formatStateUsing(fn($state) => $state ? 'Ver Archivo' : '-')
                    ->url(fn($record) => $record->path_archivo_adjunto ? Storage::disk('public')->url($record->path_archivo_adjunto) : null, shouldOpenInNewTab: true)
                    ->visible(fn($record) => !empty($record->path_archivo_adjunto)),
                Tables\Columns\IconColumn::make('path_archivo_adjunto')
                    ->label('Adjunto')
                    ->boolean() // Muestra un ícono si el campo tiene un valor (true), otro si es null (false)
                    ->trueIcon('heroicon-o-paper-clip')
                    ->falseIcon('heroicon-o-x-mark'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'Borrador' => 'Borrador',
                        'Enviada' => 'Enviada',
                        'Aprobada' => 'Aprobada',
                        'Rechazada' => 'Rechazada',
                        'Procesada' => 'Procesada',
                        'Anulada' => 'Anulada',
                    ]),
                Tables\Filters\SelectFilter::make('tipo_requisicion')
                    ->options([
                        'BIENES' => 'Bienes',
                        'SERVICIO' => 'Servicio',
                        'MATERIALES Y SUMINISTROS' => 'Materiales y Suministros',
                    ]),
                Tables\Filters\SelectFilter::make('periodo_id')
                    ->label('Período')
                    ->relationship('periodo', 'nombre')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                // Acción para generar PDF (la añadiremos después)
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
            'index' => Pages\ListRequisicions::route('/'),
            'create' => Pages\CreateRequisicion::route('/create'),
            'edit' => Pages\EditRequisicion::route('/{record}/edit'),
        ];
    }
}
