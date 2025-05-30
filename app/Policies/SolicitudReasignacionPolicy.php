<?php

namespace App\Policies;

use App\Models\SolicitudReasignacion;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SolicitudReasignacionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_any_solicitudes_reasignacion') || $user->hasPermissionTo('view_own_solicitudes_reasignacion');
    }

    public function view(User $user, SolicitudReasignacion $solicitud): bool
    {
        if ($user->hasPermissionTo('view_any_solicitudes_reasignacion')) {
            return true;
        }
        if ($user->hasPermissionTo('view_own_solicitudes_reasignacion') && $solicitud->user_id_solicitante === $user->id) { // Asumiendo que tienes user_id_solicitante
            return true;
        }
        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_solicitud_reasignacion');
    }

    public function update(User $user, SolicitudReasignacion $solicitud): bool
    {
        if ($user->hasRole('Administrador')) return true; // El admin puede editar siempre

        if (!$user->hasPermissionTo('edit_solicitud_reasignacion')) {
            return false;
        }
        // Lógica de estado: Solo editar si está 'Elaborada' o 'Rechazada'
        return in_array($solicitud->estado_solicitud, ['Elaborada', 'Rechazada']);
    }

    public function delete(User $user, SolicitudReasignacion $solicitud): bool
    {
        if (!$user->hasPermissionTo('delete_solicitud_reasignacion')) {
            return false;
        }
        // Lógica de estado: Solo eliminar si está 'Elaborada' o 'Anulada'
        return in_array($solicitud->estado_solicitud, ['Elaborada', 'Anulada']);
    }

    // --- Métodos para Flujo de Trabajo ---
    public function approve(User $user, SolicitudReasignacion $solicitud): bool
    {
        return $user->hasPermissionTo('approve_solicitud_reasignacion') && $solicitud->estado_solicitud === 'Elaborada';
    }

    public function execute(User $user, SolicitudReasignacion $solicitud): bool
    {
        return $user->hasPermissionTo('execute_solicitud_reasignacion') && $solicitud->estado_solicitud === 'Aprobada';
    }

    public function cancel(User $user, SolicitudReasignacion $solicitud): bool
    {
        return $user->hasPermissionTo('cancel_solicitud_reasignacion') &&
               !in_array($solicitud->estado_solicitud, ['Ejecutada', 'Anulada']);
    }

    public function viewPdf(User $user, SolicitudReasignacion $solicitud): bool
    {
        if ($user->hasPermissionTo('view_reasignacion_pdf')) {
            return $this->view($user, $solicitud); // Reutiliza la lógica de view()
        }
        return false;
    }
}
