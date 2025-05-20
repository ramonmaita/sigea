<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Resetear la caché de roles y permisos
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // --- CREAR PERMISOS ---
        // Permisos Generales del Panel
        Permission::updateOrCreate(
            ['name' => 'access_admin_panel'], // Nombre del permiso (llave única)
            ['description' => 'Permite el acceso general al panel de administración.'] // Descripción
        );

        // Permisos para Gestión de Usuarios
        Permission::updateOrCreate(
            ['name' => 'manage_users'],
            ['description' => 'Permite crear, ver, editar y eliminar usuarios del sistema.']
        );

        // Permisos para Gestión de Roles y Permisos
        Permission::updateOrCreate(
            ['name' => 'manage_roles_permissions'],
            ['description' => 'Permite gestionar roles (crear, editar, eliminar) y asignarles permisos.']
        );

        // Permisos para Configuración de Oficina
        Permission::updateOrCreate(
            ['name' => 'manage_office_settings'],
            ['description' => 'Permite editar la configuración de la oficina (nombre, RIF, logo, autoridades).']
        );

        // Permisos para Gestión de Períodos
        Permission::updateOrCreate(
            ['name' => 'manage_periods'],
            ['description' => 'Permite crear, ver, editar y eliminar períodos de gestión fiscal/administrativa.']
        );

        // Permisos para Requisiciones
        Permission::updateOrCreate(['name' => 'view_any_requisiciones'], ['description' => 'Permite ver todas las requisiciones del sistema.']);
        Permission::updateOrCreate(['name' => 'view_own_requisiciones'], ['description' => 'Permite ver únicamente las requisiciones creadas por el propio usuario.']);
        Permission::updateOrCreate(['name' => 'create_requisicion'], ['description' => 'Permite crear nuevas requisiciones.']);
        Permission::updateOrCreate(['name' => 'edit_requisicion'], ['description' => 'Permite editar requisiciones (podría estar restringido por estado).']);
        Permission::updateOrCreate(['name' => 'delete_requisicion'], ['description' => 'Permite eliminar requisiciones (podría estar restringido por estado).']);
        Permission::updateOrCreate(['name' => 'approve_requisicion'], ['description' => 'Permite aprobar o rechazar requisiciones.']);
        Permission::updateOrCreate(['name' => 'process_requisicion'], ['description' => 'Permite marcar requisiciones como procesadas.']);
        Permission::updateOrCreate(['name' => 'cancel_requisicion'], ['description' => 'Permite anular requisiciones.']);
        Permission::updateOrCreate(['name' => 'view_requisicion_attachments'], ['description' => 'Permite ver/descargar archivos adjuntos de requisiciones.']);
        Permission::updateOrCreate(['name' => 'upload_requisicion_attachments'], ['description' => 'Permite subir archivos adjuntos a requisiciones.']);
        Permission::updateOrCreate(['name' => 'view_any_bienes'], ['description' => 'Permite ver todos los bienes del inventario.']);
        Permission::updateOrCreate(['name' => 'view_bien'], ['description' => 'Permite ver el detalle de un bien específico.']); // A menudo se usa junto con view_any o view_own
        Permission::updateOrCreate(['name' => 'create_bien'], ['description' => 'Permite registrar nuevos bienes en el inventario.']);
        Permission::updateOrCreate(['name' => 'edit_bien'], ['description' => 'Permite editar la información de los bienes existentes.']);
        Permission::updateOrCreate(['name' => 'delete_bien'], ['description' => 'Permite eliminar bienes del inventario (usar con precaución).']);
        Permission::updateOrCreate(['name' => 'manage_inventory_settings'], ['description' => 'Permite configurar aspectos del módulo de inventario (ej. tipos de bien, ubicaciones si se gestionan).']); // Opcional
        Permission::updateOrCreate(['name' => 'assign_bien_to_user'], ['description' => 'Permite asignar un bien a un responsable/usuario.']); // Si añadimos esta funcionalidad
        Permission::updateOrCreate(['name' => 'generate_bien_qr_code'], ['description' => 'Permite generar etiquetas o códigos QR para los bienes.']); // Para el futuro
        Permission::updateOrCreate(['name' => 'view_any_ordenes_salida'], ['description' => 'Permite ver todas las órdenes de salida.']);
        Permission::updateOrCreate(['name' => 'view_own_ordenes_salida'], ['description' => 'Permite ver solo las órdenes de salida creadas/solicitadas por el propio usuario.']);
        Permission::updateOrCreate(['name' => 'create_orden_salida'], ['description' => 'Permite crear nuevas órdenes de salida.']);
        Permission::updateOrCreate(['name' => 'edit_orden_salida'], ['description' => 'Permite editar órdenes de salida (generalmente restringido por estado).']);
        Permission::updateOrCreate(['name' => 'delete_orden_salida'], ['description' => 'Permite eliminar órdenes de salida (generalmente restringido por estado).']);
        Permission::updateOrCreate(['name' => 'approve_orden_salida'], ['description' => 'Permite aprobar órdenes de salida pendientes.']);
        Permission::updateOrCreate(['name' => 'execute_orden_salida'], ['description' => 'Permite marcar una orden de salida como ejecutada (bienes entregados).']);
        Permission::updateOrCreate(['name' => 'process_retorno_orden_salida'], ['description' => 'Permite registrar y procesar el retorno de bienes de una orden de salida.']);
        Permission::updateOrCreate(['name' => 'cancel_orden_salida'], ['description' => 'Permite anular órdenes de salida.']);
        Permission::updateOrCreate(['name' => 'view_orden_salida_pdf'], ['description' => 'Permite generar/ver el PDF de una orden de salida.']); // Permiso para el PDF
        // --- CREAR ROLES ---
        $roleAdmin = Role::updateOrCreate(['name' => 'Administrador']);
        $roleGestor = Role::updateOrCreate(['name' => 'GestorOficina']);
        $roleEmpleado = Role::updateOrCreate(['name' => 'EmpleadoSolicitante']);


        // --- ASIGNAR PERMISOS A ROLES ---
        // El Administrador tiene todos los permisos.
        // Es más seguro asignar explícitamente los permisos que se vayan creando que usar Permission::all() si algunos permisos deben ser super-específicos
        $allPermissions = Permission::pluck('name')->toArray();
        $roleAdmin->syncPermissions($allPermissions);

        // GestorOficina: permisos básicos de acceso y quizás algunos módulos específicos
        $roleGestor->syncPermissions([
            'access_admin_panel',
            // Cuando existan:
            // 'view_requisiciones',
            // 'create_requisicion',
            // 'manage_inventory',
            // 'manage_periods', // Decide si el gestor puede administrar períodos
        ]);

        // EmpleadoSolicitante: permisos muy limitados
        $roleEmpleado->syncPermissions([
            'access_admin_panel',
            // Cuando existan:
            // 'create_requisicion',
            // 'view_own_documents', // Un permiso para ver solo sus propios documentos
        ]);


        // --- ASIGNAR ROL DE ADMINISTRADOR AL USUARIO ADMIN ---
        $adminEmail = 'admin@gmail.com';
        $adminUser = User::where('email', $adminEmail)->first();

        if ($adminUser) {
            if (!$adminUser->hasRole('Administrador')) {
                $adminUser->assignRole('Administrador');
                $this->command->info("Rol 'Administrador' asignado a {$adminEmail}.");
            } else {
                $this->command->info("Usuario {$adminEmail} ya tiene el rol 'Administrador'.");
            }
        } else {
            // Si no se encontró por email, intentar con el ID 1 (común para el primer usuario creado)
            $adminUserById = User::find(1);
            if ($adminUserById) {
                if (!$adminUserById->hasRole('Administrador')) {
                    $adminUserById->assignRole('Administrador');
                    $this->command->info("Rol 'Administrador' asignado al usuario con ID 1.");
                } else {
                    $this->command->info("Usuario con ID 1 ya tiene el rol 'Administrador'.");
                }
            } else {
                $this->command->warn("No se encontró el usuario administrador con email '{$adminEmail}' ni con ID 1 para asignarle el rol 'Administrador'. Por favor, asígnalo manualmente o verifica el email/ID.");
            }
        }

        $this->command->info('Seeder de Roles y Permisos ejecutado exitosamente.');
    }
}
