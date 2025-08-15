<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use App\Policies\PermissionPolicy;
use Spatie\Permission\Models\Role as SpatieRole; 
use App\Policies\RolePolicy; 

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\CategoriaCliente::class => \App\Policies\CategoriaClientePolicy::class,
    \App\Models\OrdenProduccion::class => \App\Policies\OrdenProduccionPolicy::class,
    \App\Models\Rendimiento::class => \App\Policies\RendimientoPolicy::class,
        //CategoriaProducto::class => CategoriaProductoPolicy::class,
        //SubcategoriaProducto::class => SubcategoriaProductoPolicy::class,
        SpatieRole::class => RolePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
