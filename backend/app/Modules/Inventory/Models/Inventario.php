<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Product\Models\Producto;

class Inventario extends Model
{
    use HasFactory;

    protected $table = 'inventario';

    protected $fillable = [
        'producto_id',
        'almacen_id',
        'cantidad_actual',
        'stock_minimo',
        'ultima_actualizacion',
    ];

    protected $casts = [
        'cantidad_actual' => 'decimal:4',
        'stock_minimo' => 'decimal:4',
        'ultima_actualizacion' => 'datetime',
    ];

    const CREATED_AT = null;
    const UPDATED_AT = 'ultima_actualizacion';

    public $incrementing = false;
    protected $primaryKey = ['producto_id', 'almacen_id'];

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($inventario) {
            $inventario->ultima_actualizacion = now();
        });
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
     * Scope: Stock bajo
     */
    public function scopeStockBajo($query)
    {
        return $query->whereRaw('cantidad_actual <= stock_minimo');
    }

    /**
     * Scope: Con stock disponible
     */
    public function scopeConStock($query)
    {
        return $query->where('cantidad_actual', '>', 0);
    }

    /**
     * Scope: Por almacén
     */
    public function scopePorAlmacen($query, $almacenId)
    {
        return $query->where('almacen_id', $almacenId);
    }

    /**
     * Accessor: Stock bajo (boolean)
     */
    public function getEsStockBajoAttribute()
    {
        return $this->cantidad_actual <= $this->stock_minimo;
    }

    /**
     * Accessor: Porcentaje de stock disponible
     */
    public function getPorcentajeStockAttribute()
    {
        if ($this->stock_minimo == 0) {
            return 100;
        }
        return round(($this->cantidad_actual / $this->stock_minimo) * 100, 2);
    }

    /**
     * Accessor: Cantidad a reponer
     */
    public function getCantidadReponerAttribute()
    {
        $diferencia = $this->stock_minimo - $this->cantidad_actual;
        return $diferencia > 0 ? $diferencia : 0;
    }

    /**
     * Accessor: Nivel de stock (bajo, medio, normal)
     */
    public function getNivelStockAttribute()
    {
        if ($this->cantidad_actual <= $this->stock_minimo) {
            return 'BAJO';
        } elseif ($this->cantidad_actual <= ($this->stock_minimo * 1.5)) {
            return 'MEDIO';
        } else {
            return 'NORMAL';
        }
    }

    /**
     * Aumentar stock (para claves compuestas)
     */
    public function aumentarStock($cantidad)
    {
        return self::where('producto_id', $this->producto_id)
            ->where('almacen_id', $this->almacen_id)
            ->update([
                'cantidad_actual' => $this->cantidad_actual + $cantidad,
                'ultima_actualizacion' => now()
            ]);
    }

    /**
     * Reducir stock (para claves compuestas)
     */
    public function reducirStock($cantidad)
    {
        if ($this->cantidad_actual < $cantidad) {
            throw new \Exception('Stock insuficiente');
        }
        return self::where('producto_id', $this->producto_id)
            ->where('almacen_id', $this->almacen_id)
            ->update([
                'cantidad_actual' => $this->cantidad_actual - $cantidad,
                'ultima_actualizacion' => now()
            ]);
    }
}