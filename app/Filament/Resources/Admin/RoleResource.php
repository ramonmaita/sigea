<?php

namespace App\Filament\Resources\Admin;

use App\Filament\Resources\Admin\RoleResource\Pages;
use App\Filament\Resources\Admin\RoleResource\RelationManagers;
use Spatie\Permission\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Spatie\Permission\Models\Permission;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-finger-print';
    protected static ?string $modelLabel = 'Rol';
    protected static ?string $pluralModelLabel = 'Roles';
    protected static ?string $navigationGroup = 'Administración de Acceso'; // Agrupamos aquí

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre del Rol')
                    ->required()
                    ->unique(ignoreRecord: true, table: 'roles', column: 'name') // Validar unicidad
                    ->maxLength(255),
                Forms\Components\CheckboxList::make('permissions') // Usamos CheckboxList
                    ->label('Permisos Asignados')
                    ->relationship(name: 'permissions', titleAttribute: 'name') // 'name' del modelo Permission
                    ->getOptionLabelFromRecordUsing(function (Permission $record) { // Usamos nuestro modelo App\Models\Permission
                        return "{$record->name} ({$record->description})"; // Muestra nombre y descripción
                    })
                    ->searchable() // Para buscar permisos
                    ->columns(2) // Ajusta el número de columnas según te parezca
                    ->bulkToggleable() // Botón para seleccionar/deseleccionar todos
                    ->helperText('Marque los permisos que este rol debe tener.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre del Rol')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label('Nº de Permisos')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
