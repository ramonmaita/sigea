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

        // --- PRÓXIMOS PERMISOS (EJEMPLOS PARA CUANDO CREEMOS LOS MÓDULOS) ---
        // Requisiciones
        // Permission::updateOrCreate(['name' => 'view_requisiciones'], ['description' => 'Permite ver la lista de requisiciones.']);
        // Permission::updateOrCreate(['name' => 'create_requisicion'], ['description' => 'Permite crear nuevas requisiciones.']);
        // Permission::updateOrCreate(['name' => 'edit_requisicion'], ['description' => 'Permite editar requisiciones existentes.']);
        // Permission::updateOrCreate(['name' => 'approve_requisicion'], ['description' => 'Permite aprobar o rechazar requisiciones.']);
        // Permission::updateOrCreate(['name' => 'delete_requisicion'], ['description' => 'Permite eliminar requisiciones.']);

        // Inventario
        // Permission::updateOrCreate(['name' => 'manage_inventory'], ['description' => 'Permite gestionar el inventario de bienes (crear, editar, eliminar).']);
        // Permission::updateOrCreate(['name' => 'generate_inventory_reports'], ['description' => 'Permite generar reportes del inventario.']);


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
        // **IMPORTANTE**: Cambia 'tu_email_admin@example.com' por el email real de tu cuenta de administrador.
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
