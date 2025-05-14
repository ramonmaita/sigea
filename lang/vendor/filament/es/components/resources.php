<?php

// lang/vendor/filament/es/resources.php

return [

    /*
    |--------------------------------------------------------------------------
    | Resource
    |--------------------------------------------------------------------------
    |
    | The following language lines are used by the Filament admin panel.
    |
    */

    'breadcrumb' => ':label', // Esta es una clave general, pero las de abajo son más específicas.
                              // :label será reemplazado por el getPluralModelLabel() de tu recurso.
                              // Por ejemplo, si getPluralModelLabel() es 'Roles', el breadcrumb podría ser 'Roles'.

    'label' => ':label', // Usado en algunos contextos, :label es el getModelLabel().

    'navigation_group' => ':label', // :label es el getNavigationGroup() de tu recurso.

    'pages' => [

        'list' => [
            'breadcrumb' => 'Listar', // El texto para la acción de listar en el breadcrumb.
                                      // Ejemplo: Dashboard / Roles / Listar (si "Roles" es el pluralModelLabel)
                                      // A veces Filament es suficientemente inteligente para no mostrar "Listar"
                                      // si ya estás en la página de lista principal del recurso.
            'title' => ':label',      // El título de la página de listado. :label es el getPluralModelLabel().
                                      // Ejemplo: "Roles"
        ],

        'create' => [
            'breadcrumb' => 'Crear',  // El texto para la acción de crear en el breadcrumb.
                                      // Ejemplo: Dashboard / Roles / Crear
            'title' => 'Crear :label',// El título de la página de creación. :label es el getModelLabel().
                                      // Ejemplo: "Crear Rol"
            'actions' => [
                'create' => [
                    'label' => 'Crear', // Botón principal de creación
                ],
                'create_another' => [
                    'label' => 'Crear y añadir otro', // Botón de "Crear y crear otro"
                ],
            ],
        ],

        'edit' => [
            'breadcrumb' => 'Editar',  // El texto para la acción de editar en el breadcrumb.
                                      // Ejemplo: Dashboard / Roles / Editar
            'title' => 'Editar :label', // El título de la página de edición. :label es el getModelLabel()
                                        // seguido usualmente por el título del registro (ej. "Editar Rol: Administrador")
            'actions' => [
                'save' => [
                    'label' => 'Guardar cambios', // Botón de guardar
                ],
            ],
        ],

        'view' => [
            'breadcrumb' => 'Ver',    // El texto para la acción de ver en el breadcrumb.
            'title' => 'Ver :label',  // El título de la página de visualización. :label es el getModelLabel()
                                      // seguido usualmente por el título del registro.
        ],

    ],

    'actions' => [ // Acciones generales que pueden aparecer en notificaciones o modales
        'cancel' => [
            'label' => 'Cancelar',
        ],
        'delete' => [
            'label' => 'Eliminar',
        ],
        'restore' => [
            'label' => 'Restaurar',
        ],
        // ... y otras acciones ...
    ],

    'modals' => [ // Textos para modales (ej. confirmación de borrado)
        'delete' => [
            'heading' => 'Eliminar :label',
            'subheading' => '¿Estás seguro de que deseas eliminar este :label? Esta acción no se puede deshacer.',
            'actions' => [
                'delete' => [
                    'label' => 'Eliminar',
                ],
            ],
        ],
        // ... otros modales ...
    ],

    // Puedes añadir o modificar más claves según lo que encuentres en el archivo original en inglés.
];
