<?php

namespace App\Policies;

use App\Models\SolicitudDesincorporacion;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SolicitudDesincorporacionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_any_solicitudes_desincorporacion') || $user->hasPermissionTo('view_own_solicitudes_desincorporacion');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SolicitudDesincorporacion $solicitud): bool
    {
        if ($user->hasPermissionTo('view_any_solicitudes_desincorporacion')) {
            return true;
        }
        if ($user->hasPermissionTo('view_own_solicitudes_desincorporacion')) {
            // Asume que tienes 'user_id_solicitante' en tu modelo SolicitudDesincorporacion
            // return $user->id === $solicitud->user_id_solicitante;
            // Si no tienes user_id_solicitante, esta lógica necesita ajuste o el permiso es menos granular
            return false; // Ajustar si se añade user_id_solicitante
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_solicitud_desincorporacion');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SolicitudDesincorporacion $solicitud): bool
    {
        if ($user->hasRole('Administrador')) return true; // El admin siempre puede editar (tu sugerencia anterior)

        if (!$user->hasPermissionTo('edit_solicitud_desincorporacion')) {
            return false;
        }
        // Solo se puede editar si está en estado 'Elaborada' o 'Rechazada' (para corregir)
        return in_array($solicitud->estado_solicitud, ['Elaborada', 'Rechazada']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SolicitudDesincorporacion $solicitud): bool
    {
        if (!$user->hasPermissionTo('delete_solicitud_desincorporacion')) {
            return false;
        }
        // Solo se puede eliminar si está en estado 'Elaborada' o 'Anulada'
        return in_array($solicitud->estado_solicitud, ['Elaborada', 'Anulada']);
    }

    // --- Métodos para Acciones de Flujo de Trabajo ---
    public function approve(User $user, SolicitudDesincorporacion $solicitud): bool
    {
        return $user->hasPermissionTo('approve_solicitud_desincorporacion') &&
               in_array($solicitud->estado_solicitud, ['Elaborada', 'En Revisión Técnica']);
    }

    public function execute(User $user, SolicitudDesincorporacion $solicitud): bool
    {
        // Solo se puede ejecutar si está aprobada
        return $user->hasPermissionTo('execute_solicitud_desincorporacion') && $solicitud->estado_solicitud === 'Aprobada';
    }

    public function cancel(User $user, SolicitudDesincorporacion $solicitud): bool
    {
        // No se puede anular si ya está ejecutada o anulada
        return $user->hasPermissionTo('cancel_solicitud_desincorporacion') &&
               !in_array($solicitud->estado_solicitud, ['Ejecutada', 'Anulada']);
    }

    public function viewPdf(User $user, SolicitudDesincorporacion $solicitud): bool
    {
        if ($user->hasPermissionTo('view_desincorporacion_pdf')) {
            return $this->view($user, $solicitud); // Reutiliza la lógica de view()
        }
        return false;
    }
}
