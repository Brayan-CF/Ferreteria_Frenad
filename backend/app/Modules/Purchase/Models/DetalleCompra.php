<?php

namespace Modules\Purchase\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Traits\HasAudit;
use Modules\Product\Models\Producto;
use Modules\Product\Models\UnidadMedida;
use Modules\Inventory\Models\Almacen;

class DetalleCompra extends Model
{
    use HasFactory, HasAudit;

    protected $table = 'detalle_compras';

    protected $fillable = [
        'compra_id',
        'producto_id',
        'cantidad',
        'unidad_id',
        'precio_unitario',
        'subtotal',
        'almacen_destino_id',
        'creado_por',
    ];

    protected $casts = [
        'cantidad' => 'decimal:4',
        'precio_unitario' => 'decimal:2',
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
                $detalle->subtotal = $detalle->cantidad * $detalle->precio_unitario;
            }
        });
    }

    /**
     * Relación con Compra
     */
    public function compra()
    {
        return $this->belongsTo(Compra::class, 'compra_id');
    }

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
     * Relación con Almacén Destino
     */
    public function almacenDestino()
    {
        return $this->belongsTo(Almacen::class, 'almacen_destino_id');
    }
}