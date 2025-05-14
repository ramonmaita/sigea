<?php

namespace App\Filament\Resources\Admin;

use App\Filament\Resources\Admin\PermissionResource\Pages;
use App\Filament\Resources\Admin\PermissionResource\RelationManagers;
use Spatie\Permission\Models\Permission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static ?string $navigationIcon = 'heroicon-o-key'; // Ícono para permisos
    protected static ?string $modelLabel = 'Permiso';
    protected static ?string $pluralModelLabel = 'Permisos';
    protected static ?string $navigationGroup = 'Administración de Acceso'; // Mismo grupo que Roles y Usuarios

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre del Permiso (llave)')
                    ->required()
                    ->unique(ignoreRecord: true, table: 'permissions', column: 'name') // Único
                    ->helperText('Nombre único para el permiso, ej: "ver_reportes", "editar_usuarios". Se recomienda definir nuevos permisos principalmente en el código/seeders.')
                    // Al editar, podríamos querer hacerlo de solo lectura si los nombres de los permisos están muy atados al código.
                    ->disabled(fn (string $context): bool => $context === 'edit') // Deshabilitado en edición
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('Descripción del Permiso')
                    ->required()
                    ->rows(3)
                    ->helperText('Explica claramente qué permite hacer este permiso.')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre (Llave)')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->searchable()
                    ->limit(50) // Opcional: limitar longitud en tabla
                    ->tooltip(fn (Permission $record): string => $record->description), // Opcional: tooltip completo
                    // ->wrap(), // Para que el texto se ajuste si es largo
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
                // Considerar cuidadosamente si se debe permitir eliminar permisos desde la UI,
                // ya que pueden estar en uso por roles o directamente en el código.
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers\RolesRelationManager::class, // Para ver qué roles tienen este permiso
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermissions::route('/'),
            'create' => Pages\CreatePermission::route('/create'), // Habilitar si quieres creación desde UI
            'edit' => Pages\EditPermission::route('/{record}/edit'),
        ];
    }
}
