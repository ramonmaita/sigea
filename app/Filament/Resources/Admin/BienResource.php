<?php

namespace App\Filament\Resources\Admin;

use App\Filament\Resources\Admin\BienResource\Pages;
use App\Filament\Resources\Admin\BienResource\RelationManagers;
use App\Models\Bien;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BienResource extends Resource
{
    protected static ?string $model = Bien::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box'; // Ícono para inventario/bienes
    protected static ?string $modelLabel = 'Bien';
    protected static ?string $pluralModelLabel = 'Bienes del Inventario';
    protected static ?string $navigationGroup = 'Gestión de Inventario'; // Nuevo grupo

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información del Bien')
                    ->columns(2)
                    ->schema([
                        TextInput::make('codigo_bien')
                            ->label('Código del Bien')
                            ->required() // Ahora es requerido y editable
                            ->unique(ignoreRecord: true, table: 'biens', column: 'codigo_bien') // Validación de unicidad
                            ->maxLength(255) // Longitud máxima apropiada
                            ->columnSpanFull(), // Ocupa todo el ancho de la sección
                        TextInput::make('nombre')
                            ->label('Nombre o Descripción Corta')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('serial_numero')
                            ->label('Número de Serial (si aplica)')
                            ->nullable()
                            ->unique(ignoreRecord: true, table: 'biens', column: 'serial_numero')
                            ->maxLength(255),
                        TextInput::make('valor_adquisicion')
                            ->label('Valor de Adquisición')
                            ->numeric()
                            ->prefix(config('app.currency_symbol', 'Bs.'))
                            ->nullable()
                            ->minValue(0),
                        Select::make('estado_bien')
                            ->label('Estado del Bien')
                            ->options([
                                'Nuevo' => 'Nuevo',
                                'Bueno' => 'Bueno',
                                'Regular' => 'Regular',
                                'En Mantenimiento' => 'En Mantenimiento',
                                'Deteriorado' => 'Deteriorado',
                                'Para Desincorporar' => 'Para Desincorporar',
                                'Desincorporado' => 'Desincorporado',
                            ])
                            ->default('Bueno')
                            ->required(),
                        TextInput::make('ubicacion_actual')
                            ->label('Ubicación Actual')
                            ->required()
                            ->maxLength(255),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('codigo_bien')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre del Bien')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->tooltip(fn(Bien $record) => $record->nombre),
                Tables\Columns\TextColumn::make('serial_numero')
                    ->label('Serial')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado_bien')
                    ->label('Estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Nuevo' => 'success',
                        'Bueno' => 'primary',
                        'Regular' => 'warning',
                        'En Mantenimiento' => 'info',
                        'Deteriorado' => 'danger',
                        'Para Desincorporar' => 'gray',
                        'Desincorporado' => 'danger', // O un color distintivo
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ubicacion_actual')
                    ->label('Ubicación')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('valor_adquisicion')
                    ->label('Valor Adq.')
                    ->money(config('app.currency_shortcode', 'VES'), true) // ej. VES, USD. El true es para el símbolo.
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Registro')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado_bien')
                    ->label('Estado')
                    ->options([
                        'Nuevo' => 'Nuevo',
                        'Bueno' => 'Bueno',
                        'Regular' => 'Regular',
                        'En Mantenimiento' => 'En Mantenimiento',
                        'Deteriorado' => 'Deteriorado',
                        'Para Desincorporar' => 'Para Desincorporar',
                        'Desincorporado' => 'Desincorporado',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                // Podríamos añadir una acción para generar etiqueta con QR más adelante
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListBiens::route('/'),
            'create' => Pages\CreateBien::route('/create'),
            'edit' => Pages\EditBien::route('/{record}/edit'),
        ];
    }
}
