<?php

namespace App\Filament\Resources\Admin;

use App\Filament\Resources\Admin\ComunicacionResource\Pages;
use App\Filament\Resources\Admin\ComunicacionResource\RelationManagers;
use App\Models\Comunicacion;
use App\Models\DetalleOficina;
use App\Models\Periodo;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ComunicacionResource extends Resource
{
    protected static ?string $model = Comunicacion::class;
    protected static ?string $navigationIcon = 'heroicon-o-envelope'; // O 'heroicon-o-chat-bubble-left-right'
    protected static ?string $modelLabel = 'Comunicación';
    protected static ?string $pluralModelLabel = 'Comunicaciones';
    protected static ?string $navigationGroup = 'Gestión de Correspondencia'; // Nuevo grupo

    public static function form(Form $form): Form
    {
        // Obtener autoridades para el firmante
        $detalleOficina = DetalleOficina::first();
        $opcionesAutoridades = [];
        if ($detalleOficina && !empty($detalleOficina->autoridades)) {
            foreach ($detalleOficina->autoridades as $autoridad) {
                if (isset($autoridad['nombre']) && isset($autoridad['cargo'])) {
                    // Usamos una clave compuesta para poder separar nombre y cargo después
                    $opcionesAutoridades[$autoridad['nombre'] . '|' . $autoridad['cargo']] = ($autoridad['nombre']) . ' - (' . ($autoridad['cargo']) . ')';
                }
            }
        }

        return $form
            ->schema([
                Section::make('Encabezado de la Comunicación')
                    ->columns(3)
                    ->schema([
                        DatePicker::make('fecha_documento')
                            ->label('Fecha del Documento')
                            ->default(now())
                            ->required(),
                        Select::make('periodo_id')
                            ->label('Período de Gestión')
                            ->options(Periodo::query()->orderBy('anio', 'desc')->pluck('nombre', 'id'))
                            ->default(function () {
                                // Asumiendo que tienes un estado 'Abierto' en tu modelo Periodo
                                $periodoActivo = Periodo::where('estado', 'Abierto')->orderBy('anio', 'desc')->first();
                                return $periodoActivo ? $periodoActivo->id : null;
                            })
                            ->searchable()
                            ->required(),
                        Select::make('tipo_comunicacion')
                            ->label('Tipo de Comunicación')
                            ->options([
                                'Memorando' => 'Memorando',
                                'Oficio' => 'Oficio',
                                'Circular Interna' => 'Circular Interna',
                            ])
                            ->required(),
                    ]),
                Section::make('Contenido y Destinatarios')
                    ->columns(1) // O 2 si quieres campos lado a lado
                    ->schema([
                        TextInput::make('asunto')
                            ->label('Asunto')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('dirigido_a_nombre')
                            ->label('Dirigido a (Nombre/Cargo/Dpto.)')
                            ->required()
                            ->maxLength(255)
                            ->datalist(
                                Comunicacion::query() // Autocompletar de comunicaciones previas
                                    ->select('dirigido_a_nombre')
                                    ->distinct()
                                    ->pluck('dirigido_a_nombre')
                                    ->toArray()
                            )
                            ->columnSpanFull(),
                        TextInput::make('dirigido_a_cargo_dependencia')
                            ->label('Cargo/Dependencia Específica del Destinatario')
                            ->nullable()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        RichEditor::make('cuerpo') // Editor Enriquecido
                            ->label('Cuerpo de la Comunicación')
                            ->required()
                            ->toolbarButtons([ // Personaliza los botones si quieres
                                'attachFiles',
                                'blockquote',
                                'bold',
                                'bulletList',
                                'codeBlock',
                                'h2',
                                'h3',
                                'italic',
                                'link',
                                'orderedList',
                                'redo',
                                'strike',
                                'table', // Botón de tabla
                                'undo',
                            ])
                            ->columnSpanFull(),
                        // Para 'con_copia_a' como JSON usando Repeater
                        Repeater::make('con_copia_a')
                            ->label('Con Copia A (CC)')
                            ->schema([
                                TextInput::make('destinatario_cc')
                                    ->label('Nombre/Cargo/Dpto. CC')
                                    ->required(),
                            ])
                            ->addActionLabel('Añadir Destinatario CC')
                            ->collapsible()
                            ->defaultItems(0)
                            ->columnSpanFull(),
                        TextInput::make('referencia')
                            ->label('Referencia (si aplica)')
                            ->nullable()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),
                Section::make('Firma y Estado')
                    ->columns(2)
                    ->schema([
                        Select::make('firmante_seleccionado') // Campo "virtual" para seleccionar la autoridad
                            ->label('Firmante (Autoridad de la Oficina)')
                            ->options($opcionesAutoridades)
                            ->live()
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                if ($state) {
                                    list($nombre, $cargo) = explode('|', $state, 2);
                                    $set('firmante_nombre', $nombre);
                                    $set('firmante_cargo', $cargo);
                                } else {
                                    $set('firmante_nombre', null);
                                    $set('firmante_cargo', null);
                                }
                            })
                            ->searchable()
                            ->dehydrated(false)
                            ->required(),
                        TextInput::make('firmante_nombre')
                            ->label('Nombre del Firmante (Automático)')
                            ->disabled()
                            ->hidden(true)
                            ->dehydrated(), // Para que se guarde
                        TextInput::make('firmante_cargo')
                            ->label('Cargo del Firmante (Automático)')
                            ->disabled()
                            ->hidden(true)
                            ->dehydrated(), // Para que se guarde
                        Select::make('estado')
                            ->label('Estado de la Comunicación')
                            ->options([
                                'Borrador' => 'Borrador',
                                'Enviada' => 'Enviada',
                                'Anulada' => 'Anulada',
                            ])
                            ->default('Borrador')
                            ->required(),
                    ]),
                Section::make('Archivos Adjuntos')
                    ->schema([
                        FileUpload::make('path_adjuntos')
                            ->label('Adjuntar Archivos (Opcional)')
                            ->multiple() // Permitir múltiples archivos
                            ->disk('public')
                            ->directory('adjuntos_comunicaciones/' . date('Y/m'))
                            ->visibility('public') // O 'private'
                            ->reorderable()
                            ->appendFiles() // Para añadir más archivos al editar sin perder los anteriores
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_comunicacion')->label('N° Doc.')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('fecha_documento')->date('d/m/Y')->label('Fecha')->sortable(),
                Tables\Columns\TextColumn::make('tipo_comunicacion')->label('Tipo')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('asunto')->searchable()->limit(40)->tooltip(fn($record) => $record->asunto),
                Tables\Columns\TextColumn::make('dirigido_a_nombre')->label('Dirigido A')->searchable()->limit(30),
                Tables\Columns\TextColumn::make('firmante_nombre')->label('Firmante')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('estado')->label('Estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Borrador' => 'gray',
                        'Enviada' => 'success',
                        'Anulada' => 'danger',
                        default => 'gray',
                    })
                    ->searchable()->sortable(),
                // Tables\Columns\TextColumn::make('creador.name')->label('Elaborado por')->searchable()->sortable(), // Si tienes la relación
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipo_comunicacion')->options([/* tus tipos */]),
                Tables\Filters\SelectFilter::make('estado')->options([/* tus estados */]),
                Tables\Filters\SelectFilter::make('periodo_id')->relationship('periodo', 'nombre')->label('Período'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Action::make('pdfComunicacion')
                    ->label('Ver PDF')
                    ->icon('heroicon-o-document-text') // O el que prefieras
                    ->color('info')
                    ->modalContent(fn(Comunicacion $record): \Illuminate\Contracts\View\View => view(
                        'filament.modals.view-pdf', // Reutilizar el modal genérico
                        ['pdfUrl' => route('comunicaciones.pdf', $record)]
                    ))
                    ->modalHeading(fn(Comunicacion $record): string => 'Comunicación N° ' . $record->numero_comunicacion)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->modalWidth('6xl') // Los documentos pueden necesitar más ancho
                    ->visible(
                        fn(Comunicacion $record): bool =>
                        in_array($record->estado, ['Borrador','Enviada', 'Archivada']) // O según tu lógica
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('fecha_documento', 'desc');
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
            'index' => Pages\ListComunicacions::route('/'),
            'create' => Pages\CreateComunicacion::route('/create'),
            'edit' => Pages\EditComunicacion::route('/{record}/edit'),
        ];
    }
}
