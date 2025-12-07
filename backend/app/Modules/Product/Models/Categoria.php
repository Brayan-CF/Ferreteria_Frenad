<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Traits\HasAudit;

class Categoria extends Model
{
    use HasFactory, HasAudit;

    protected $table = 'categorias';

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
        'creado_por',
        'actualizado_por',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    /**
     * Relación con Productos
     */
    public function productos()
    {
        return $this->hasMany(Producto::class, 'categoria_id');
    }

    /**
     * Scope: Solo categorías activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope: Con cantidad de productos
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

    /**
     * Accessor: Total de productos activos
     */
    public function getTotalProductosActivosAttribute()
    {
        return $this->productos()->where('activo', true)->count();
    }
}