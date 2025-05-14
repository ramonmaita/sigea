<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Administrador');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->hasRole('Administrador');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('Administrador');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return $user->hasRole('Administrador');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
         // Solo un admin puede borrar usuarios, y no puede borrarse a sí mismo
        if (!$user->hasRole('Administrador')) {
            return false;
        }
        // Prevenir que un admin se borre a sí mismo
        if ($user->id === $model->id) {
            return false;
        }
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->hasRole('Administrador');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        // Prevenir que un admin se borre a sí mismo
        if ($user->id === $model->id) {
            return false;
        }
        return $user->hasRole('Administrador');
    }
}
