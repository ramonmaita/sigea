<?php

namespace App\Policies;

use App\Models\OrdenSalida;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrdenSalidaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_any_ordenes_salida');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, OrdenSalida $ordenSalida): bool
    {
        return $user->hasPermissionTo('view_any_ordenes_salida');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_orden_salida');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, OrdenSalida $ordenSalida): bool
    {
        // Si el usuario es Administrador, puede editar en cualquier estado
        if ($user->hasRole('Administrador')) { // O un permiso específico como 'bypass_orden_salida_status_edit'
            return true;
        }

        // Lógica para otros roles (ej. solo pueden editar si está 'Solicitada')
        if ($user->hasPermissionTo('edit_orden_salida')) {
            return in_array($ordenSalida->estado_orden, ['Solicitada']);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, OrdenSalida $ordenSalida): bool
    {
        if (!$user->hasPermissionTo('delete_orden_salida')) {
            return false;
        }
        // Lógica adicional: Solo se puede eliminar si está en ciertos estados
        return in_array($ordenSalida->estado_orden, ['Solicitada', 'Anulada']); // Ejemplo simple
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, OrdenSalida $ordenSalida): bool
    {
        return $user->hasPermissionTo('delete_orden_salida');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, OrdenSalida $ordenSalida): bool
    {
        return $user->hasPermissionTo('delete_orden_salida');
    }

    // --- Métodos para Acciones de Flujo de Trabajo ---
    public function approve(User $user, OrdenSalida $ordenSalida): bool
    {
        return $user->hasPermissionTo('approve_orden_salida') && $ordenSalida->estado_orden === 'Solicitada';
    }

    public function execute(User $user, OrdenSalida $ordenSalida): bool
    {
        return $user->hasPermissionTo('execute_orden_salida') && $ordenSalida->estado_orden === 'Aprobada';
    }

    public function processReturn(User $user, OrdenSalida $ordenSalida): bool
    {
        // Solo si es un tipo de salida que espera retorno y está ejecutada o parcialmente retornada
        $tiposConRetorno = ['Préstamo Interno', 'Préstamo Externo', 'Reparación', 'Evento Especial'];
        return $user->hasPermissionTo('process_retorno_orden_salida') &&
            in_array($ordenSalida->tipo_salida, $tiposConRetorno) &&
            in_array($ordenSalida->estado_orden, ['Ejecutada (Bienes Entregados)', 'Retornada Parcialmente']);
    }

    public function cancel(User $user, OrdenSalida $ordenSalida): bool
    {
        // Se puede anular si no está ya procesada completamente o cerrada (para definitivas) o ya anulada
        return $user->hasPermissionTo('cancel_orden_salida') &&
            !in_array($ordenSalida->estado_orden, ['Retornada Completamente', 'Cerrada', 'Anulada']);
    }

    public function viewPdf(User $user, OrdenSalida $ordenSalida): bool
    {
        // Reutiliza la lógica de 'view' o define una específica si es necesario
        if ($user->hasPermissionTo('view_orden_salida_pdf')) {
            if ($user->hasPermissionTo('view_any_ordenes_salida')) {
                return true;
            }
        }
        // Por ahora, si puede ver el detalle (view), puede ver el PDF. Ajustar si es necesario.
        return $this->view($user, $ordenSalida);
    }
}
