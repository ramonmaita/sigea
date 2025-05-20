<?php

namespace App\Filament\Resources\Admin;

use App\Filament\Resources\Admin\OrdenSalidaResource\Pages;
use App\Filament\Resources\Admin\OrdenSalidaResource\RelationManagers;
use App\Models\Bien;
use App\Models\OrdenSalida;
use App\Models\Periodo;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrdenSalidaResource extends Resource
{
    protected static ?string $model = OrdenSalida::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck'; // Ícono para órdenes de salida
    protected static ?string $modelLabel = 'Orden de Salida';
    protected static ?string $pluralModelLabel = 'Órdenes de Salida';
    protected static ?string $navigationGroup = 'Gestión de Inventario'; // Mismo grupo que Bienes

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información General de la Orden')
                    ->columns(2)
                    ->schema([
                        TextInput::make('numero_orden_salida')
                            ->label('N° de Orden de Salida')
                            ->disabled() // Se generará al guardar
                            ->placeholder('Se genera al guardar')
                            ->dehydrated(false) // No se envía si está deshabilitado y no se modifica
                            ->columnSpanFull(),
                        DatePicker::make('fecha_salida')
                            ->label('Fecha de Salida')
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
                        Select::make('tipo_salida')
                            ->label('Tipo de Salida')
                            ->options([
                                'Préstamo Interno' => 'Préstamo Interno',
                                'Préstamo Externo' => 'Préstamo Externo',
                                'Reparación' => 'Reparación',
                                'Traslado Definitivo' => 'Traslado Definitivo',
                                'Evento Especial' => 'Evento Especial',
                                'Para Desincorporar' => 'Para Desincorporar',
                            ])
                            ->required()
                            ->live() // Importante para la visibilidad condicional de la sección del proveedor
                            ->columnSpanFull(), // Ocupa todo el ancho si está solo en su "sub-rejilla" o ajusta
                        DatePicker::make('fecha_retorno_prevista')
                            ->label('Fecha Retorno Prevista (si aplica)')
                            ->nullable()
                            // Mostrar solo si el tipo de salida no implica un traslado definitivo o desincorporación
                            ->visible(fn(Get $get): bool => !in_array($get('tipo_salida'), ['Traslado Definitivo', 'Para Desincorporar'])),
                        Select::make('estado_orden') // Movido aquí para consistencia, puedes reubicarlo
                            ->label('Estado de la Orden')
                            ->options([
                                'Solicitada' => 'Solicitada',
                                'Aprobada' => 'Aprobada',
                                'Ejecutada (Bienes Entregados)' => 'Ejecutada (Bienes Entregados)',
                                'Retornada Parcialmente' => 'Retornada Parcialmente',
                                'Retornada Completamente' => 'Retornada Completamente',
                                'Cerrada' => 'Cerrada',
                                'Anulada' => 'Anulada'
                            ])
                            ->default('Solicitada')
                            ->required(),
                    ]),
                Section::make('Destino y Responsable del Retiro')
                    ->columns(2)
                    ->schema([
                        TextInput::make('destino_o_unidad_receptora')
                            ->label('Destino / Unidad Receptora')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('persona_responsable_retiro')
                            ->label('Persona Responsable del Retiro')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('cedula_responsable_retiro')
                            ->label('Cédula Responsable Retiro')
                            ->nullable()
                            ->maxLength(20),
                    ]),

                Section::make('Información del Proveedor (para Reparación)')
                    ->columns(2)
                    ->visible(fn(Get $get): bool => $get('tipo_salida') === 'Reparación')
                    ->schema([
                        TextInput::make('proveedor_nombre')
                            ->label('Nombre del Proveedor')
                            ->maxLength(255)
                            ->required(fn(Get $get): bool => $get('tipo_salida') === 'Reparación'), // Requerido si es reparación
                        Textarea::make('proveedor_direccion')
                            ->label('Dirección del Proveedor')
                            ->rows(2)
                            ->columnSpanFull(),
                        TextInput::make('proveedor_telefono')
                            ->label('Teléfono del Proveedor')
                            ->maxLength(50),
                        // TextInput::make('proveedor_rif') // Si lo añades a la migración
                        //     ->label('RIF del Proveedor')
                        //     ->maxLength(20),
                    ]),

                Textarea::make('justificacion')
                    ->label('Justificación de la Salida')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(), // Ocupa todo el ancho del formulario principal si no está en una sección con columnas

                Section::make('Bienes a Incluir en la Orden')
                    ->schema([
                        Select::make('biens') // Nombre de la relación 'biens' en el modelo OrdenSalida
                            ->label('Seleccionar Bienes')
                            ->multiple()
                            ->relationship(name: 'biens', titleAttribute: 'nombre') // 'nombre' del modelo Bien
                            ->getOptionLabelFromRecordUsing(function (Bien $record) { // Cambiado a una función anónima completa para claridad
                                $serial = $record->serial_numero ?? 'N/A'; // Resolvemos el serial primero
                                return "{$record->codigo_bien} - {$record->nombre} (S/N: {$serial})";
                            })
                            ->preload(20)
                            ->searchable(['codigo_bien', 'nombre', 'serial_numero'])
                            // Filtrar bienes que pueden salir (ej. no desincorporados o ya en otra orden activa)
                            // Este filtro puede ser complejo y necesitar una consulta más elaborada.
                            // Por ahora, un filtro simple por estado:
                            ->optionsLimit(200) // Limitar la cantidad de opciones cargadas inicialmente para búsqueda
                            ->getSearchResultsUsing(function (string $search) {
                                return Bien::whereIn('estado_bien', ['Nuevo', 'Bueno', 'Regular', 'En Mantenimiento'])
                                    ->where(function (Builder $query) use ($search) {
                                        $query->where('nombre', 'like', "%{$search}%")
                                            ->orWhere('codigo_bien', 'like', "%{$search}%")
                                            ->orWhere('serial_numero', 'like', "%{$search}%");
                                    })
                                    ->limit(50)
                                    ->pluck('nombre', 'id') // Esto podría necesitar ajustarse si queremos el objeto Bien completo para getOptionLabelFromRecordUsing
                                    ->mapWithKeys(function ($nombre, $id) { // Para usar getOptionLabelFromRecordUsing
                                        $bien = Bien::find($id);
                                        // --- LÍNEA ORIGINAL CON POSIBLE ERROR ---
                                        // return $bien ? [$id => "{$bien->codigo_bien} - {$bien->nombre} (S/N: {$bien->serial_numero ?? 'N/A'})"] : [];
                                        // --- LÍNEA CORREGIDA ---
                                        if ($bien) {
                                            $serial = $bien->serial_numero ?? 'N/A';
                                            return [$id => "{$bien->codigo_bien} - {$bien->nombre} (S/N: {$serial})"];
                                        } else {
                                            return [];
                                        }
                                    });
                            })
                            ->helperText('Seleccione uno o más bienes. Solo se muestran bienes disponibles.')
                            ->required() // Al menos un bien debe ser seleccionado
                            ->columnSpanFull(),
                    ]),
                Textarea::make('observaciones')
                    ->label('Observaciones Adicionales')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_orden_salida')->label('N° Orden')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('fecha_salida')->date('d/m/Y')->label('Fecha Salida')->sortable(),
                Tables\Columns\TextColumn::make('tipo_salida')->label('Tipo')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('destino_o_unidad_receptora')->label('Destino')->searchable()->limit(30),
                Tables\Columns\TextColumn::make('persona_responsable_retiro')->label('Responsable Retiro')->searchable(),
                Tables\Columns\TextColumn::make('estado_orden')->label('Estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Solicitada' => 'warning',
                        'Aprobada' => 'primary',
                        'Ejecutada (Bienes Entregados)' => 'success',
                        'Retornada Parcialmente' => 'info',
                        'Retornada Completamente' => 'success',
                        'Cerrada' => 'gray',
                        'Anulada' => 'danger',
                        default => 'secondary',
                    })
                    ->searchable()->sortable(),
                Tables\Columns\TextColumn::make('periodo.nombre')->label('Período')->searchable()->sortable(),
                // Tables\Columns\TextColumn::make('user_solicitante.name')->label('Solicitante')->searchable()->sortable(), // Si añades la relación
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipo_salida')
                    ->options([ /* tus tipos */]),
                Tables\Filters\SelectFilter::make('estado_orden')
                    ->options([ /* tus estados */]),
                Tables\Filters\SelectFilter::make('periodo_id')->relationship('periodo', 'nombre')->label('Período'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    // Solo visible si la orden está en un estado editable y el usuario tiene permiso
                    ->visible(
                        fn(OrdenSalida $record): bool =>
                        auth()->user()->can('update') || in_array($record->estado_orden, ['Solicitada']) && auth()->user()->can('update', $record)
                    ),

                ActionGroup::make([ // Agrupamos las acciones de flujo
                    Action::make('aprobarOrden')
                        ->label('Aprobar')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Aprobar Orden de Salida')
                        ->modalDescription('¿Está seguro de que desea aprobar esta orden de salida?')
                        ->action(function (OrdenSalida $record) {
                            // Aquí no necesitas volver a verificar el permiso con $user->can() si la visibilidad ya lo hizo,
                            // pero no está de más si quieres doble seguridad o si la lógica es compleja.
                            // La policy se ejecutará para el método 'approve' si lo defines.
                            $record->estado_orden = 'Aprobada';
                            // $record->user_id_aprobador = auth()->id(); // Si tuvieras este campo
                            // $record->fecha_aprobacion = now();      // Si tuvieras este campo
                            $record->save();
                            Notification::make()->title('Orden Aprobada')->success()->send();
                        })
                        ->visible(
                            fn(OrdenSalida $record): bool =>
                            $record->estado_orden === 'Solicitada' && auth()->user()->can('approve', $record)
                        ),

                    Action::make('ejecutarOrden')
                        ->label('Marcar como Entregada')
                        ->icon('heroicon-o-truck') // O 'heroicon-o-check-badge'
                        ->color('primary')
                        ->requiresConfirmation()
                        ->action(function (OrdenSalida $record) {
                            $record->estado_orden = 'Ejecutada (Bienes Entregados)';
                            // Aquí podrías añadir lógica para cambiar el estado de los bienes asociados
                            // Por ejemplo, si un bien tiene un estado 'En Almacén', cambiarlo a 'En Préstamo' o 'En Reparación'
                            // foreach ($record->biens as $bien) {
                            //     $bien->estado_bien = 'En Préstamo'; // O según $record->tipo_salida
                            //     $bien->save();
                            // }
                            $record->save();
                            Notification::make()->title('Orden Marcada como Entregada')->success()->send();
                        })
                        ->visible(
                            fn(OrdenSalida $record): bool =>
                            $record->estado_orden === 'Aprobada' && auth()->user()->can('execute', $record)
                        ),

                    Action::make('procesarRetorno')
                        ->label('Procesar Retorno de Bienes')
                        ->icon('heroicon-o-archive-box-arrow-down')
                        ->color('info')
                        ->modalHeading('Registrar Retorno de Bienes')
                        ->modalWidth('7xl') // O más grande si es necesario
                        ->form(function (OrdenSalida $record) { // El formulario se construye dinámicamente
                            $camposFormulario = [];
                            $camposFormulario[] = DatePicker::make('fecha_retorno_real_accion')
                                ->label('Fecha Real de Retorno (General)')
                                ->default(now())
                                ->required();

                            // Listar bienes pendientes de esta orden para marcar su retorno
                            // Usaremos un Repeater para permitir actualizar el estado_item_retorno y observacion_item_salida
                            $itemsParaRepeater = [];
                            foreach ($record->biens as $bien) { // 'biens' es la relación en OrdenSalida
                                $itemsParaRepeater[] = [
                                    'bien_id' => $bien->id,
                                    'nombre_bien' => $bien->codigo_bien . ' - ' . $bien->nombre,
                                    'estado_item_retorno_actual' => $bien->pivot->estado_item_retorno, // Desde la tabla pivote
                                    'observacion_item_salida_actual' => $bien->pivot->observacion_item_salida,
                                ];
                            }

                            $camposFormulario[] = Repeater::make('items_retorno')
                                ->label('Estado de Retorno de los Bienes')
                                ->schema([
                                    Hidden::make('bien_id'),
                                    TextInput::make('nombre_bien')
                                        ->label('Bien')
                                        ->disabled(),
                                    Select::make('estado_item_retorno_nuevo')
                                        ->label('Estado de Retorno del Ítem')
                                        ->options([
                                            'Retornado' => 'Retornado OK',
                                            'Retornado con Daños' => 'Retornado con Daños',
                                            // 'Extraviado' => 'Extraviado/No Retornado',
                                            // Otros estados que necesites para el ítem
                                        ])
                                        ->default(fn(Get $get) => $get('estado_item_retorno_actual')), // Valor actual
                                    Textarea::make('observacion_item_salida_nueva')
                                        ->label('Observación del Ítem al Retornar')
                                        ->rows(1)
                                        ->default(fn(Get $get) => $get('observacion_item_salida_actual')), // Valor actual
                                ])
                                ->default($itemsParaRepeater) // Llenar con los bienes de la orden
                                ->columns(3)
                                ->disabled(fn(OrdenSalida $record) => !in_array($record->estado_orden, ['Ejecutada (Bienes Entregados)', 'Retornada Parcialmente'])) // Prevenir edición si la orden ya está cerrada
                                ->addable(false) // No se pueden añadir nuevos bienes aquí
                                ->orderable(false) // No se pueden reordenar bienes aquí
                                // ->collapsible()
                                ->deletable(false); // No se pueden quitar bienes de la lista aquí

                            $camposFormulario[] = Select::make('estado_final_orden')
                                ->label('Actualizar Estado General de la Orden')
                                ->options([
                                    'Retornada Parcialmente' => 'Retornada Parcialmente',
                                    'Retornada Completamente' => 'Retornada Completamente',
                                    'Cerrada' => 'Cerrada (Préstamo Concluido)',
                                ])
                                ->required()
                                ->default(function (OrdenSalida $record) {
                                    // Lógica para sugerir el estado final
                                    // Si todos los items_retorno marcan 'Retornado', sugerir 'Retornada Completamente'
                                    return $record->estado_orden; // O una lógica más inteligente
                                });
                            return $camposFormulario;
                        })
                        ->action(function (OrdenSalida $record, array $data) {
                            $record->fecha_retorno_real = $data['fecha_retorno_real_accion'];

                            // Actualizar estado y observación de cada bien en la tabla pivote
                            if (isset($data['items_retorno'])) {
                                foreach ($data['items_retorno'] as $itemRetornoData) {
                                    $bienId = $itemRetornoData['bien_id'];
                                    // Actualizar la tabla pivote
                                    $record->biens()->updateExistingPivot($bienId, [
                                        'estado_item_retorno' => $itemRetornoData['estado_item_retorno_nuevo'],
                                        'observacion_item_salida' => $itemRetornoData['observacion_item_salida_nueva'],
                                    ]);

                                    // Actualizar el estado_bien del modelo Bien si es necesario
                                    $bien = Bien::find($bienId);
                                    if ($bien) {
                                        if ($itemRetornoData['estado_item_retorno_nuevo'] === 'Retornado' || $itemRetornoData['estado_item_retorno_nuevo'] === 'Retornado con Daños') {
                                            // Aquí decides a qué estado vuelve el bien principal.
                                            // Podría ser 'Bueno', 'Regular', o el estado que tenía antes de salir,
                                            // o un nuevo estado 'Disponible para Revisión' si volvió con daños.
                                            $bien->estado_bien = 'Bueno'; // Ejemplo
                                            $bien->save();
                                        }
                                        // Lógica para 'Extraviado', etc.
                                    }
                                }
                            }

                            // Determinar si todos los bienes han sido retornados (o su estado final definido)
                            $todosRetornados = true;
                            foreach ($record->biens()->withPivot('estado_item_retorno')->get() as $bienEnOrden) {
                                if (!in_array($bienEnOrden->pivot->estado_item_retorno, ['Retornado', 'Retornado con Daños', 'Extraviado'])) { // O los estados que consideres finales para un item
                                    $todosRetornados = false;
                                    break;
                                }
                            }

                            if ($todosRetornados && $data['estado_final_orden'] === 'Retornada Parcialmente') {
                                // Si todos los items ya tienen un estado final, pero el usuario seleccionó "Parcialmente",
                                // quizás forzar a "Completamente" o "Cerrada".
                                Notification::make()->title('Advertencia: Todos los ítems tienen un estado final. Considere "Retornada Completamente".')->warning()->send();
                            }

                            // Si el usuario seleccionó un estado final, lo usamos. Sino, decidimos.
                            if (isset($data['estado_final_orden'])) {
                                $record->estado_orden = $data['estado_final_orden'];
                            } else {
                                // Lógica automática si no se selecciona estado_final_orden
                                if ($todosRetornados) {
                                    $record->estado_orden = 'Retornada Completamente';
                                } else {
                                    $record->estado_orden = 'Retornada Parcialmente';
                                }
                            }


                            $record->save();
                            Notification::make()->title('Retorno de Bienes Procesado')->success()->send();
                        })
                        ->visible(
                            fn(OrdenSalida $record): bool =>
                            in_array($record->estado_orden, ['Ejecutada (Bienes Entregados)', 'Retornada Parcialmente']) &&
                                in_array($record->tipo_salida, ['Préstamo Interno', 'Préstamo Externo', 'Reparación', 'Evento Especial']) &&
                                auth()->user()->can('processReturn', $record)
                        ),

                    // Action::make('procesarRetorno')
                    //     ->label('Procesar Retorno')
                    //     ->icon('heroicon-o-archive-box-arrow-down')
                    //     ->color('info')
                    //     // Esta acción podría necesitar un formulario en el modal para
                    //     // seleccionar qué bienes retornaron y su estado de retorno si es complejo.
                    //     // Por ahora, una acción simple que cambia el estado general.
                    //     ->requiresConfirmation()
                    //     ->form([ // Ejemplo de formulario en modal
                    //         Select::make('estado_final_orden')
                    //             ->label('Estado Final de la Orden Post-Retorno')
                    //             ->options([
                    //                 'Retornada Completamente' => 'Retornada Completamente',
                    //                 'Retornada Parcialmente' => 'Retornada Parcialmente',
                    //                 // Podrías añadir 'Cerrada' aquí si el retorno cierra la orden
                    //             ])
                    //             ->required(),
                    //         DatePicker::make('fecha_retorno_real_accion')
                    //             ->label('Fecha Real de Retorno')
                    //             ->default(now())
                    //             ->required(),
                    //         // Aquí podrías tener un repeater o checklist para los bienes retornados
                    //         // y actualizar 'estado_item_retorno' en la tabla pivote.
                    //         // Esto es más avanzado.
                    //     ])
                    //     ->action(function (OrdenSalida $record, array $data) {
                    //         $record->estado_orden = $data['estado_final_orden'];
                    //         $record->fecha_retorno_real = $data['fecha_retorno_real_accion'];
                    //         // Lógica para actualizar estado de bienes individuales (avanzado)
                    //         // foreach ($record->biens as $bien) {
                    //         //     // Lógica para marcar el bien como retornado o actualizar su estado_item_retorno
                    //         //     // $bien->estado_bien = 'Bueno'; // O el estado original
                    //         //     // $bien->save();
                    //         // }
                    //         $record->save();
                    //         Notification::make()->title('Retorno Procesado')->success()->send();
                    //     })
                    //     ->visible(
                    //         fn(OrdenSalida $record): bool =>
                    //         in_array($record->estado_orden, ['Ejecutada (Bienes Entregados)', 'Retornada Parcialmente']) &&
                    //             in_array($record->tipo_salida, ['Préstamo Interno', 'Préstamo Externo', 'Reparación', 'Evento Especial']) && // Solo tipos que esperan retorno
                    //             auth()->user()->can('processReturn', $record) // Usando el método de la Policy
                    //     ),

                    Action::make('cerrarOrdenDefinitiva') // Para salidas que no tienen retorno
                        ->label('Cerrar Orden (Salida Definitiva)')
                        ->icon('heroicon-o-lock-closed')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(function (OrdenSalida $record) {
                            $record->estado_orden = 'Cerrada';
                            // Actualizar estado de los bienes a 'Trasladado' o 'Desincorporado'
                            foreach ($record->biens as $bien) {
                                if ($record->tipo_salida === 'Para Desincorporar') {
                                    $bien->estado_bien = 'Desincorporado';
                                } elseif ($record->tipo_salida === 'Traslado Definitivo') {
                                    // Podrías tener un estado 'Trasladado' o simplemente actualizar ubicación/responsable
                                    // $bien->estado_bien = 'Trasladado';
                                }
                                $bien->save();
                            }
                            $record->save();
                            Notification::make()->title('Orden Cerrada (Definitiva)')->success()->send();
                        })
                        ->visible(
                            fn(OrdenSalida $record): bool =>
                            $record->estado_orden === 'Ejecutada (Bienes Entregados)' &&
                                in_array($record->tipo_salida, ['Traslado Definitivo', 'Para Desincorporar']) &&
                                auth()->user()->can('execute', $record) // O un permiso específico 'close_definitive_orden_salida'
                        ),

                    Action::make('anularOrden')
                        ->label('Anular')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Anular Orden de Salida')
                        ->form([
                            Textarea::make('motivo_anulacion')
                                ->label('Motivo de la Anulación')
                                ->required(),
                        ])
                        ->action(function (OrdenSalida $record, array $data) {
                            $record->estado_orden = 'Anulada';
                            // Podrías guardar $data['motivo_anulacion'] en un campo 'motivo_anulacion' en OrdenSalida
                            // $record->motivo_anulacion = $data['motivo_anulacion'];
                            // Si los bienes fueron marcados como "en salida", revertir su estado si es necesario
                            $record->save();
                            Notification::make()->title('Orden Anulada')->success()->send();
                        })
                        ->visible(
                            fn(OrdenSalida $record): bool =>
                            !in_array($record->estado_orden, ['Ejecutada (Bienes Entregados)', 'Retornada Completamente', 'Cerrada', 'Anulada']) &&
                                auth()->user()->can('cancel', $record)
                        ),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(), // Considera si quieres borrado masivo
                ]),
            ])
            // ->actions([
            //     Tables\Actions\ViewAction::make(),
            //     Tables\Actions\EditAction::make(),
            //     // Acciones para aprobar, ejecutar, etc. vendrán después
            // ])
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([
            //         Tables\Actions\DeleteBulkAction::make(),
            //     ]),
            // ])
            ->defaultSort('fecha_salida', 'desc');
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
            'index' => Pages\ListOrdenSalidas::route('/'),
            'create' => Pages\CreateOrdenSalida::route('/create'),
            'edit' => Pages\EditOrdenSalida::route('/{record}/edit'),
        ];
    }
}
