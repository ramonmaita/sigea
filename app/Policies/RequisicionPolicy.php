<?php

namespace App\Policies;

use App\Models\Requisicion;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RequisicionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Puede ver la lista si tiene permiso para ver todas o al menos las suyas
        return $user->hasPermissionTo('view_any_requisiciones') || $user->hasPermissionTo('view_own_requisiciones');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Requisicion $requisicion): bool
    {
        if ($user->hasPermissionTo('view_any_requisiciones')) {
            return true;
        }

        if ($user->hasPermissionTo('view_own_requisiciones')) {
            return $user->id === $requisicion->user_id; // Solo si es el creador
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_requisicion');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Requisicion $requisicion): bool
    {
        if (!$user->hasPermissionTo('edit_requisicion')) {
            return false;
        }
        // Ejemplo: Solo se puede editar si está en estado 'Borrador'
        // O si el usuario tiene un permiso de "editar todas las requisiciones"
        if ($requisicion->estado === 'Borrador') {
            // Y quizás solo si es el creador
            // return $user->id === $requisicion->user_id;
            return true; // Simplificado por ahora, el permiso 'edit_requisicion' da acceso
        }
        // Podrías tener un permiso 'force_edit_requisicion' para administradores
        return false; // O $user->hasPermissionTo('force_edit_requisicion');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Requisicion $requisicion): bool
    {
        if (!$user->hasPermissionTo('delete_requisicion')) {
            return false;
        }
        // Ejemplo: Solo se puede eliminar si está en estado 'Borrador'
        // return $requisicion->estado === 'Borrador';
        return true; // Simplificado por ahora
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Requisicion $requisicion): bool
    {
        // Si usas SoftDeletes y tienes un permiso para restaurar
        return $user->hasPermissionTo('delete_requisicion'); // O un permiso específico como 'restore_requisicion'
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Requisicion $requisicion): bool
    {
        return $user->hasPermissionTo('delete_requisicion'); // O 'force_delete_requisicion'
    }

    // Podrías añadir métodos para las otras acciones:
    public function approve(User $user, Requisicion $requisicion): bool
    {
        return $user->hasPermissionTo('approve_requisicion') && $requisicion->estado === 'Enviada';
    }

    public function process(User $user, Requisicion $requisicion): bool
    {
        return $user->hasPermissionTo('process_requisicion') && $requisicion->estado === 'Aprobada';
    }

    public function cancel(User $user, Requisicion $requisicion): bool
    {
        if (!$user->hasPermissionTo('cancel_requisicion')) return false;
        // Solo se puede anular si no está ya procesada o anulada, por ejemplo
        return !in_array($requisicion->estado, ['Procesada', 'Anulada']);
    }
}
