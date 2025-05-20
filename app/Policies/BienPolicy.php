<?php

namespace App\Policies;

use App\Models\Bien;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BienPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_any_bienes');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Bien $bien): bool
    {
        return $user->hasPermissionTo('view_any_bienes') || $user->hasPermissionTo('view_bien');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_bien');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Bien $bien): bool
    {
        return $user->hasPermissionTo('edit_bien');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Bien $bien): bool
    {
        return $user->hasPermissionTo('delete_bien');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Bien $bien): bool
    {
        return $user->hasPermissionTo('delete_bien');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Bien $bien): bool
    {
        return $user->hasPermissionTo('delete_bien');
    }
}
