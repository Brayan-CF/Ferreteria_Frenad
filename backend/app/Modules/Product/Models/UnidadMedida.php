<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadMedida extends Model
{
    use HasFactory;

    protected $table = 'unidades_medida';

    protected $fillable = [
        'nombre',
        'abreviatura',
        'tipo',
        'activo',
    ];

    protected $casts = [
        'creado_en' => 'datetime',
        'activo' => 'boolean',
    ];

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null;

    /**
     * Tipos de unidad disponibles
     */
    const TIPOS = [
        'longitud',
        'peso',
        'volumen',
        'unidad',
        'area',
    ];

    /**
     * Relación con Productos (como unidad base)
     */
    public function productos()
    {
        return $this->hasMany(Producto::class, 'unidad_base_id');
    }

    /**
     * Relación con ProductoUnidades
     */
    public function productoUnidades()
    {
        return $this->hasMany(ProductoUnidad::class, 'unidad_id');
    }

    /**
     * Scope: Por tipo
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }
}