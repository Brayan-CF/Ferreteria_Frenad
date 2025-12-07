<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoUnidad extends Model
{
    use HasFactory;

    protected $table = 'producto_unidades';

    protected $fillable = [
        'producto_id',
        'unidad_id',
        'factor_conversion',
        'es_unidad_compra',
        'es_unidad_venta',
    ];

    protected $casts = [
        'factor_conversion' => 'decimal:4',
        'es_unidad_compra' => 'boolean',
        'es_unidad_venta' => 'boolean',
        'creado_en' => 'datetime',
    ];

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null;

    /**
     * Relación con Producto
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /**
     * Relación con Unidad de Medida
     */
    public function unidad()
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_id');
    }

    /**
     * Scope: Unidades de compra
     */
    public function scopeParaCompra($query)
    {
        return $query->where('es_unidad_compra', true);
    }

    /**
     * Scope: Unidades de venta
     */
    public function scopeParaVenta($query)
    {
        return $query->where('es_unidad_venta', true);
    }

    /**
     * Convertir cantidad de esta unidad a unidad base
     */
    public function convertirABase($cantidad)
    {
        return $cantidad * $this->factor_conversion;
    }

    /**
     * Convertir cantidad de unidad base a esta unidad
     */
    public function convertirDesdeBase($cantidadBase)
    {
        if ($this->factor_conversion == 0) {
            return 0;
        }
        return $cantidadBase / $this->factor_conversion;
    }
}