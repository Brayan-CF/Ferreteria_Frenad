<?php

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla
     */
    protected $table = 'roles';

    /**
     * Atributos asignables en masa
     */
    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    /**
     * Atributos que deben ser casteados
     */
    protected $casts = [
        'creado_en' => 'datetime',
    ];

    /**
     * Nombre de las columnas de timestamps personalizadas
     */
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null; // No tiene updated_at

    /**
     * Relación muchos a muchos con Usuarios
     */
    public function usuarios()
    {
        return $this->belongsToMany(
            Usuario::class,
            'usuario_roles',
            'rol_id',
            'usuario_id'
        )->withTimestamps();
    }

    /**
     * Scope: Buscar por nombre
     */
    public function scopePorNombre($query, string $nombre)
    {
        return $query->where('nombre', $nombre);
    }

    /**
     * Constantes de roles del sistema
     */
    const ADMINISTRADOR = 'Administrador';
    const VENDEDOR = 'Vendedor';
    const BODEGUERO = 'Bodeguero';

    /**
     * Obtener todos los nombres de roles disponibles
     */
    public static function rolesDisponibles(): array
    {
        return [
            self::ADMINISTRADOR,
            self::VENDEDOR,
            self::BODEGUERO,
        ];
    }
}