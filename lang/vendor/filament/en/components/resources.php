<?php
// Ejemplo:
return [

    'pages' => [
        'create' => [
            // 'breadcrumb' => 'Create', // Ya lo deberías tener como 'Crear' por el pluralModelLabel
            'title' => 'Crear :label', // Cambiar a 'Crear :label'
        ],
        'edit' => [
            // 'breadcrumb' => 'Edit',
            'title' => 'Edit :label', // Cambiar a 'Editar :label'
        ],
        'view' => [
            // 'breadcrumb' => 'View',
            'title' => 'View :label', // Cambiar a 'Ver :label'
        ],
        'list' => [
            'breadcrumb' => 'List', // Cambiar a 'Listar' o dejar que tome el pluralModelLabel
            // 'title' => ':label', // El título de la lista usualmente es el pluralModelLabel
        ],
    ],
];
