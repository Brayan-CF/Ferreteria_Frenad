<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Traits\HasAudit;

class Marca extends Model
{
    use HasFactory, HasAudit;

    protected $table = 'marcas';

    protected $fillable = [
        'nombre',
        'descripcion',
        'pais_origen',
        'activo',
        'creado_por',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'creado_en' => 'datetime',
    ];

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null;

    /**
     * Relación con Productos
     */
    public function productos()
    {
        return $this->hasMany(Producto::class, 'marca_id');
    }

    /**
     * Scope: Solo marcas activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope: Con conteo de productos
     */
    public function scopeConConteoProductos($query)
    {
        return $query->withCount('productos');
    }

    /**
     * Accessor: Total de productos
     */
    public function getTotalProductosAttribute()
    {
        return $this->productos()->count();
    }
}