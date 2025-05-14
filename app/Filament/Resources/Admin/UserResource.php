<?php

namespace App\Filament\Resources\Admin;

use App\Filament\Resources\Admin\UserResource\Pages;
use App\Filament\Resources\Admin\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Administración';
    protected static ?string $modelLabel = 'Usuario';
    protected static ?string $pluralModelLabel = 'Usuarios';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('cedula')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('apellido')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true), // Único, ignorando el registro actual al editar
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->required(fn(string $context): bool => $context === 'create') // Requerido solo al crear
                    ->dehydrateStateUsing(fn($state) => Hash::make($state)) // Hashear al guardar
                    ->dehydrated(fn($state) => filled($state)), // Solo enviar si está lleno (para no sobreescribir al editar si está vacío)
                // ->minLength(8), // Opcional: validación de longitud
                Forms\Components\Select::make('roles') // Para asignar roles
                    ->multiple()
                    ->relationship('roles', 'name') // 'roles' es el nombre de la relación en el modelo User, 'name' es el campo a mostrar del rol
                    ->preload() // Precargar opciones para mejor rendimiento con pocos roles
                    ->label('Roles'),
                // Nueva Sección para Permisos Directos
                Forms\Components\Section::make('Permisos Directos')
                    ->description('Asigne permisos adicionales directamente a este usuario. Estos se suman a los permisos heredados de sus roles.')
                    ->schema([
                        Forms\Components\CheckboxList::make('permissions') // El nombre de la relación en el modelo User es 'permissions'
                            ->label('Permisos Directos Asignados')
                            ->relationship(name: 'permissions', titleAttribute: 'name') // 'name' del modelo Permission
                            ->getOptionLabelFromRecordUsing(function (Permission $record) {
                                return "{$record->name} ({$record->description})"; // Muestra nombre y descripción
                            })
                            ->searchable()
                            ->columns(2) // Ajusta según prefieras (1, 2, o 3 columnas)
                            ->bulkToggleable()
                            ->helperText('Estos permisos se otorgan individualmente al usuario, además de los que obtiene a través de sus roles.'),
                    ])
                    ->collapsible() // Para que la sección se pueda plegar/desplegar
                    ->collapsed(true), // Opcional: iniciar plegada por defecto
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cedula')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('apellido')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles.name') // Mostrar los nombres de los roles
                    ->badge()
                    ->label('Roles'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Podrías añadir filtros por rol aquí si lo necesitas
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(), // Asegúrate de que el admin no se pueda borrar a sí mismo o que haya protecciones
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
