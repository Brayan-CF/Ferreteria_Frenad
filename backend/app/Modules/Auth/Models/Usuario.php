<?php

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Modules\Shared\Traits\HasAudit;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Nombre de la tabla
     */
    protected $table = 'usuarios';

    /**
     * Atributos asignables en masa
     */
    protected $fillable = [
        'nombre',
        'email',
        'password_hash',
        'activo',
    ];

    /**
     * Atributos ocultos para serialización
     */
    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    /**
     * Atributos que deben ser casteados
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'activo' => 'boolean',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    /**
     * Nombre de las columnas de timestamps personalizadas
     */
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Hashear password automáticamente
        static::creating(function ($usuario) {
            if (!empty($usuario->password)) {
                $usuario->password = bcrypt($usuario->password);
            }
        });

        static::updating(function ($usuario) {
            if ($usuario->isDirty('password') && !empty($usuario->password)) {
                $usuario->password = bcrypt($usuario->password);
            }
        });
    }

    /**
     * Relación muchos a muchos con Roles
     */
    public function roles()
    {
        return $this->belongsToMany(
            Rol::class,
            'usuario_roles',
            'usuario_id',
            'rol_id'
        )->withPivot('asignado_en', 'asignado_por');
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('nombre', $roleName)->exists();
    }

    /**
     * Verificar si el usuario tiene alguno de los roles dados
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()->whereIn('nombre', $roles)->exists();
    }

    /**
     * Verificar si el usuario tiene todos los roles dados
     */
    public function hasAllRoles(array $roles): bool
    {
        $userRoles = $this->roles()->pluck('nombre')->toArray();
        return count(array_intersect($roles, $userRoles)) === count($roles);
    }

    /**
     * Verificar si el usuario es administrador
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('Administrador');
    }

    /**
     * Verificar si el usuario es vendedor
     */
    public function isVendedor(): bool
    {
        return $this->hasRole('Vendedor');
    }

    /**
     * Verificar si el usuario es bodeguero
     */
    public function isBodeguero(): bool
    {
        return $this->hasRole('Bodeguero');
    }

    /**
     * Scope: Solo usuarios activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope: Filtrar por rol
     */
    public function scopeConRol($query, string $rol)
    {
        return $query->whereHas('roles', function ($q) use ($rol) {
            $q->where('nombre', $rol);
        });
    }

    /**
     * Accessor: Nombre completo de roles
     */
    public function getRolesNombresAttribute(): array
    {
        return $this->roles->pluck('nombre')->toArray();
    }

    /**
     * Accessor: Primer rol (para compatibilidad)
     */
    public function getRolPrincipalAttribute(): ?string
    {
        return $this->roles->first()?->nombre;
    }

    /**
     * Accessor: Mapear password_hash a password para compatibilidad
     */
    public function getPasswordAttribute()
    {
        return $this->attributes['password_hash'];
    }

    /**
     * Mutator: Al asignar password, guardar en password_hash con hash
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password_hash'] = bcrypt($value);
    }

    /**
     * Método requerido por Authenticatable
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }
}