<?php

namespace Modules\Sales\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Traits\HasAudit;
use Modules\Product\Models\Producto;
use Modules\Product\Models\UnidadMedida;
use Modules\Inventory\Models\Almacen;

class DetalleVenta extends Model
{
    use HasFactory, HasAudit;

    protected $table = 'detalle_ventas';

    protected $fillable = [
        'venta_id',
        'producto_id',
        'almacen_id',
        'cantidad',
        'unidad_id',
        'precio_unitario',
        'descuento_monto',
        'subtotal',
        'creado_por',
    ];

    protected $casts = [
        'cantidad' => 'decimal:4',
        'precio_unitario' => 'decimal:2',
        'descuento_monto' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'creado_en' => 'datetime',
    ];

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null;

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Calcular subtotal automáticamente
        static::creating(function ($detalle) {
            if (empty($detalle->subtotal)) {
                $detalle->subtotal = ($detalle->cantidad * $detalle->precio_unitario) - ($detalle->descuento_monto ?? 0);
            }
        });
    }

    /**
     * Relación con Venta
     */
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    /**
     * Relación con Producto
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /**
     * Relación con Almacén
     */
    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'almacen_id');
    }

    /**
     * Relación con Unidad de Medida
     */
    public function unidad()
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_id');
    }

    /**
     * Accessor: Costo del producto (para cálculo de utilidad)
     */
    public function getCostoTotalAttribute()
    {
        if (!$this->producto) {
            return 0;
        }
        return $this->cantidad * $this->producto->precio_compra;
    }

    /**
     * Accessor: Utilidad del item
     */
    public function getUtilidadAttribute()
    {
        return $this->subtotal - $this->costo_total;
    }

    /**
     * Accessor: Precio sin descuento
     */
    public function getPrecioSinDescuentoAttribute()
    {
        return $this->cantidad * $this->precio_unitario;
    }

    /**
     * Accessor: Porcentaje de descuento aplicado
     */
    public function getPorcentajeDescuentoAttribute()
    {
        if ($this->precio_sin_descuento == 0) {
            return 0;
        }
        return round(($this->descuento_monto / $this->precio_sin_descuento) * 100, 2);
    }
}