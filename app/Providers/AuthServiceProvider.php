<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use App\Policies\PermissionPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Permission::class => PermissionPolicy::class,
<<<<<<< HEAD
        \App\Models\CategoriaCliente::class => \App\Policies\CategoriaClientePolicy::class,
        \App\Models\OrdenProduccion::class => \App\Policies\OrdenProduccionPolicy::class,
=======
        //CategoriaProducto::class => CategoriaProductoPolicy::class,
        //SubcategoriaProducto::class => SubcategoriaProductoPolicy::class,
>>>>>>> fa6ccee06d2470b3e0eb061eb003df50aad81f43
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
