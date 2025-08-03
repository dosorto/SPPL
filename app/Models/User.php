<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Permission;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;



class User extends Authenticatable //implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles; // Eliminamos TenantScoped para evitar problemas con la autenticación

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'empresa_id',
        'empleado_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /*public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole(['admin', 'super_admin', 'editor', 'viewer']);
    }*/

    public function getFilamentAvatarUrl(): ?string
    {
        return null;
    }

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function actualizadoPor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function eliminadoPor()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }
    
    /**
     * Verifica si el usuario puede acceder a una empresa específica.
     *
     * @param int $empresaId
     * @return bool
     */
    public function canAccessEmpresa($empresaId): bool
    {
        // Los usuarios con rol 'root' pueden acceder a cualquier empresa
        if ($this->hasRole('root')) {
            return true;
        }
        
        // Los usuarios con roles admin pueden acceder a su propia empresa y a otras si hay permisos específicos
        if ($this->hasRole(['super_admin', 'admin'])) {
            // Por ahora, permitir al admin acceder a cualquier empresa
            // En el futuro, se podría implementar una tabla de permisos específicos
            return true;
        }
        
        // Para otros usuarios, solo pueden acceder a su propia empresa
        return $this->empresa_id == $empresaId;
    }
    
    /**
     * Obtiene todas las empresas a las que el usuario puede acceder.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAccessibleEmpresas()
    {
        // Si es 'root', puede ver todas las empresas
        if ($this->hasRole('root')) {
            return Empresa::all();
        }
        
        // Si es admin, puede ver todas las empresas (por ahora)
        if ($this->hasRole(['super_admin', 'admin'])) {
            return Empresa::all();
        }
        
        // Para otros usuarios, solo su propia empresa
        return Empresa::where('id', $this->empresa_id)->get();
    }
}
