<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Product\Models\Producto;
use Modules\Auth\Models\Usuario;

class MovimientoInventario extends Model
{
    use HasFactory;

    protected $table = 'movimientos_inventario';

    protected $fillable = [
        'producto_id',
        'almacen_id',
        'almacen_destino_id',
        'tipo_movimiento',
        'cantidad',
        'compra_id',
        'venta_id',
        'detalle_compra_id',
        'detalle_venta_id',
        'razon',
        'usuario_id',
    ];

    protected $casts = [
        'cantidad' => 'decimal:4',
        'creado_en' => 'datetime',
    ];

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null;

    /**
     * Tipos de movimiento disponibles
     */
    const ENTRADA_COMPRA = 'ENTRADA_COMPRA';
    const SALIDA_VENTA = 'SALIDA_VENTA';
    const TRANSFERENCIA = 'TRANSFERENCIA';
    const AJUSTE_POSITIVO = 'AJUSTE_POSITIVO';
    const AJUSTE_NEGATIVO = 'AJUSTE_NEGATIVO';
    const DEVOLUCION_VENTA = 'DEVOLUCION_VENTA';
    const DEVOLUCION_COMPRA = 'DEVOLUCION_COMPRA';

    /**
     * Relación con Producto
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /**
     * Relación con Almacén (origen)
     */
    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'almacen_id');
    }

    /**
     * Relación con Almacén (destino)
     */
    public function almacenDestino()
    {
        return $this->belongsTo(Almacen::class, 'almacen_destino_id');
    }

    /**
     * Relación con Usuario que realizó el movimiento
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * Scope: Por tipo de movimiento
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_movimiento', $tipo);
    }

    /**
     * Scope: Por rango de fechas
     */
    public function scopeEntreFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('creado_en', [$fechaInicio, $fechaFin]);
    }

    /**
     * Scope: Por producto
     */
    public function scopePorProducto($query, $productoId)
    {
        return $query->where('producto_id', $productoId);
    }

    /**
     * Scope: Por almacén
     */
    public function scopePorAlmacen($query, $almacenId)
    {
        return $query->where('almacen_id', $almacenId)
                    ->orWhere('almacen_destino_id', $almacenId);
    }

    /**
     * Scope: Por usuario
     */
    public function scopePorUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    /**
     * Accessor: Es entrada de stock
     */
    public function getEsEntradaAttribute()
    {
        return in_array($this->tipo_movimiento, [
            self::ENTRADA_COMPRA,
            self::AJUSTE_POSITIVO,
            self::DEVOLUCION_VENTA,
        ]);
    }

    /**
     * Accessor: Es salida de stock
     */
    public function getEsSalidaAttribute()
    {
        return in_array($this->tipo_movimiento, [
            self::SALIDA_VENTA,
            self::AJUSTE_NEGATIVO,
            self::DEVOLUCION_COMPRA,
        ]);
    }

    /**
     * Accessor: Signo del movimiento (+, -, ~)
     */
    public function getSignoAttribute()
    {
        if ($this->es_entrada) {
            return '+';
        } elseif ($this->es_salida) {
            return '-';
        } else {
            return '~'; // Transferencia
        }
    }

    /**
     * Accessor: Descripción legible del movimiento
     */
    public function getDescripcionAttribute()
    {
        $descripciones = [
            self::ENTRADA_COMPRA => 'Entrada por compra',
            self::SALIDA_VENTA => 'Salida por venta',
            self::TRANSFERENCIA => 'Transferencia entre almacenes',
            self::AJUSTE_POSITIVO => 'Ajuste positivo de inventario',
            self::AJUSTE_NEGATIVO => 'Ajuste negativo de inventario',
            self::DEVOLUCION_VENTA => 'Devolución de venta',
            self::DEVOLUCION_COMPRA => 'Devolución a proveedor',
        ];

        return $descripciones[$this->tipo_movimiento] ?? $this->tipo_movimiento;
    }

    /**
     * Obtener todos los tipos de movimiento
     */
    public static function tiposDisponibles(): array
    {
        return [
            self::ENTRADA_COMPRA,
            self::SALIDA_VENTA,
            self::TRANSFERENCIA,
            self::AJUSTE_POSITIVO,
            self::AJUSTE_NEGATIVO,
            self::DEVOLUCION_VENTA,
            self::DEVOLUCION_COMPRA,
        ];
    }
}