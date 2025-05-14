<?php

namespace App\Providers;

use App\Models\Periodo;
use App\Models\User;
use App\Policies\PeriodoPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy', // Formato antiguo comentado
        Periodo::class => PeriodoPolicy::class, // <-- Añade esta línea
        User::class => UserPolicy::class, // <-- Añade esta línea
        Role::class => RolePolicy::class,
        Permission::class => PermissionPolicy::class,

    ];
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();
    }
}
