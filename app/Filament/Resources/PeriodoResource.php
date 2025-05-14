<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PeriodoResource\Pages;
use App\Filament\Resources\PeriodoResource\RelationManagers;
use App\Models\Periodo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PeriodoResource extends Resource
{
    protected static ?string $model = Periodo::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Administración';
    protected static ?string $modelLabel = 'Período de Gestión';
    protected static ?string $pluralModelLabel = 'Períodos de Gestión';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('anio')
                    ->label('Año')
                    ->required()
                    ->numeric()
                    ->minValue(2000) // O un año base razonable
                    ->maxValue(2100), // Y un tope
                Forms\Components\DatePicker::make('inicio')
                    ->required(),
                Forms\Components\DatePicker::make('fin')
                    ->required()
                    ->afterOrEqual('inicio'), // Asegurar que la fecha fin sea después o igual a la de inicio
                Forms\Components\Select::make('estado')
                    ->label('Estado')
                    ->options(Periodo::getEstados()) // Usamos el método del modelo
                    ->required(),
            ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('anio')
                    ->label('Año')
                    ->sortable(),
                Tables\Columns\TextColumn::make('inicio')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fin')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado')
                    ->badge() // Mostrar como una "badge" o etiqueta
                    ->color(fn (string $state): string => match ($state) {
                        Periodo::ESTADO_ACTIVO => 'success',
                        Periodo::ESTADO_INACTIVO => 'danger',
                        default => 'primary',
                    })
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options(Periodo::getEstados()),
                Tables\Filters\SelectFilter::make('anio')
                    ->options(
                        Periodo::orderBy('anio', 'desc')->pluck('anio', 'anio')->unique()->toArray()
                    ) // Para obtener los años existentes como opciones de filtro
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(), // Considera si quieres borrado físico o lógico (soft delete)
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('anio', 'desc'); // Ordenar por defecto por año descendente
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
            'index' => Pages\ListPeriodos::route('/'),
            'create' => Pages\CreatePeriodo::route('/create'),
            'edit' => Pages\EditPeriodo::route('/{record}/edit'),
        ];
    }
}
